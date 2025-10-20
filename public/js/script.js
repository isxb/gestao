// script.js
console.log("script.js: Arquivo carregado.");

document.addEventListener('DOMContentLoaded', () => {
    console.log("script.js: DOM completamente carregado.");

    // Tenta obter phpVars. Se não existir (ex: em páginas sem form de login), define fallbacks.
    const phpEmailError = window.phpVars?.emailError ?? null;
    const phpGeneralError = window.phpVars?.generalError ?? null;
    const allowedDomains = window.phpVars?.allowedDomains ?? ['@reframax.com.br', '@rvengenheiros.com.br']; // Fallback
    const defaultEmailPlaceholder = window.phpVars?.defaultEmailValue ?? "seu.usuario@********.com.br"; // Fallback

    const canvas = document.getElementById('shieldCanvas');
    if (canvas) {
        console.log("script.js: Elemento Canvas encontrado.");
        const ctx = canvas.getContext('2d');
        if (ctx) {
            console.log("script.js: Contexto 2D obtido.");
            // --- Configurações da Animação ---
            const numParticles = 100; // Reduzido para performance se necessário
            const particleRadius = 1.6;
            const connectionDistance = 130;
            const particleSpeed = 0.3;
            const particleColor = '#00BFFF';
            let particles = [];
            let animationFrameId;

            function resizeCanvas() {
                if (!canvas) return;
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
                initParticles();
            }

            class Particle {
                constructor(x, y) {
                    this.x = x; this.y = y;
                    this.vx = (Math.random() - 0.5) * particleSpeed * 2;
                    this.vy = (Math.random() - 0.5) * particleSpeed * 2;
                    this.radius = particleRadius * (0.5 + Math.random() * 0.7);
                    this.alpha = 0;
                    this.fadeSpeed = 0.008 + Math.random() * 0.01;
                    this.targetAlpha = 0.4 + Math.random() * 0.4;
                }
                update() {
                    this.x += this.vx; this.y += this.vy;
                    if (this.x - this.radius < 0) { this.vx *= -1; this.x = this.radius; }
                    else if (this.x + this.radius > canvas.width) { this.vx *= -1; this.x = canvas.width - this.radius; }
                    if (this.y - this.radius < 0) { this.vy *= -1; this.y = this.radius; }
                    else if (this.y + this.radius > canvas.height) { this.vy *= -1; this.y = canvas.height - this.radius; }

                    if (this.alpha < this.targetAlpha) this.alpha = Math.min(this.targetAlpha, this.alpha + this.fadeSpeed);
                    else if (this.alpha > this.targetAlpha) this.alpha = Math.max(this.targetAlpha, this.alpha - this.fadeSpeed);
                    if (Math.random() < 0.01) this.targetAlpha = 0.3 + Math.random() * 0.5;
                    this.alpha = Math.max(0, Math.min(1, this.alpha));
                }
                draw() {
                    if (this.alpha <= 0.01 || !ctx) return;
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                    const [r, g, b] = [parseInt(particleColor.substring(1,3),16), parseInt(particleColor.substring(3,5),16), parseInt(particleColor.substring(5,7),16)];
                    ctx.fillStyle = `rgba(${r}, ${g}, ${b}, ${this.alpha})`;
                    ctx.fill();
                }
            }

            function initParticles() {
                if (!canvas) return; particles = [];
                for (let i = 0; i < numParticles; i++) {
                    particles.push(new Particle(Math.random()*canvas.width, Math.random()*canvas.height));
                }
            }

            function animate() {
                if (!canvas || !ctx) return;
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                for (let i = 0; i < particles.length; i++) {
                    particles[i].update(); particles[i].draw();
                    for (let j = i + 1; j < particles.length; j++) {
                        const dx = particles[i].x - particles[j].x;
                        const dy = particles[i].y - particles[j].y;
                        const distance = Math.sqrt(dx*dx + dy*dy);
                        if (distance < connectionDistance) {
                            ctx.beginPath(); ctx.moveTo(particles[i].x, particles[i].y); ctx.lineTo(particles[j].x, particles[j].y);
                            const [r,g,b] = [parseInt(particleColor.substring(1,3),16), parseInt(particleColor.substring(3,5),16), parseInt(particleColor.substring(5,7),16)];
                            const lineOpacity = Math.max(0, (1-(distance/connectionDistance)) * particles[i].alpha * particles[j].alpha * 0.7);
                            ctx.strokeStyle = `rgba(${r},${g},${b},${lineOpacity})`;
                            ctx.lineWidth = Math.max(0.1, (1-(distance/connectionDistance)) * 1.1);
                            ctx.stroke();
                        }
                    }
                }
                animationFrameId = requestAnimationFrame(animate);
            }
            window.addEventListener('resize', resizeCanvas);
            resizeCanvas(); animate();
            console.log("script.js: Animação de partículas iniciada.");
        } else { console.error("ERRO: Contexto 2D do canvas não obtido."); }
    } else { console.log("script.js: Elemento Canvas 'shieldCanvas' não encontrado nesta página."); }

    // --- LÓGICA DE VALIDAÇÃO DE FORMULÁRIO DE LOGIN (se existir na página) ---
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email'); // Usado por outros forms também
    const emailErrorMessage = document.getElementById('emailErrorMessage'); // Específico do login
    const passwordInput = document.getElementById('password'); // Específico do login

    function setEmailErrorState(isError, message, inputElement = emailInput, messageElement = emailErrorMessage) {
        if (!inputElement || !messageElement) return;
        if (isError) {
            inputElement.classList.add('input-error');
            messageElement.textContent = message || "Por favor, use um e-mail corporativo válido.";
            messageElement.style.display = 'block';
            document.body.classList.add('email-error-bg'); // Pode ser genérico para erro no form
        } else {
            inputElement.classList.remove('input-error');
            messageElement.style.display = 'none';
            document.body.classList.remove('email-error-bg');
        }
    }

    function checkEmailDomain(emailValue) {
        // emailValue já deve estar em minúsculas e sem espaços
        return allowedDomains.some(domain => emailValue.endsWith(domain));
    }

    if (emailInput) { // Configura para qualquer input de email que precise dessa validação
        const currentDefaultEmailValue = emailInput.value === defaultEmailPlaceholder ? defaultEmailPlaceholder : "";

        emailInput.addEventListener('focus', () => {
            if (emailInput.value === currentDefaultEmailValue && currentDefaultEmailValue === defaultEmailPlaceholder) {
                emailInput.value = '';
            }
            // Limpa erro ao focar, se houver um emailErrorMessage associado
            if(emailErrorMessage) setEmailErrorState(false, "", emailInput, emailErrorMessage);
            else emailInput.classList.remove('input-error'); // Limpa erro genérico
        });

        emailInput.addEventListener('blur', () => {
            const currentValue = emailInput.value.trim();
            const currentValueLower = currentValue.toLowerCase();

            if (currentValue === '') {
                if (currentDefaultEmailValue === defaultEmailPlaceholder) emailInput.value = defaultEmailPlaceholder;
                if(emailErrorMessage) setEmailErrorState(false, "", emailInput, emailErrorMessage); // Placeholder não é erro
            } else if (currentValue !== defaultEmailPlaceholder) {
                if (!checkEmailDomain(currentValueLower)) {
                    if(emailErrorMessage) setEmailErrorState(true, "Domínio de e-mail inválido. Permitidos: " + allowedDomains.join(', '), emailInput, emailErrorMessage);
                    else emailInput.classList.add('input-error');
                } else {
                    if(emailErrorMessage) setEmailErrorState(false, "", emailInput, emailErrorMessage);
                }
            } else {
                 if(emailErrorMessage) setEmailErrorState(false, "", emailInput, emailErrorMessage);
            }
        });

        // Mostrar erro vindo do PHP (se for o formulário de login)
        if (loginForm && phpEmailError) {
            setEmailErrorState(true, phpEmailError);
        }
    }
     // Mostrar erro geral vindo do PHP (se for o formulário de login e houver um campo para isso)
    if (loginForm && phpGeneralError) {
        const generalErrorDiv = document.querySelector('.login-form-error-general'); // Supondo que você crie esta div no HTML
        if (generalErrorDiv) {
            generalErrorDiv.textContent = phpGeneralError;
            generalErrorDiv.style.display = 'block';
        }
        document.body.classList.add('general-error-bg');
    }


    if (loginForm && emailInput && passwordInput && emailErrorMessage) {
        loginForm.addEventListener('submit', (event) => {
            const currentEmailValue = emailInput.value.trim();
            const currentEmailValueLower = currentEmailValue.toLowerCase();
            let clientSideError = false;

            if (currentEmailValue === '' || currentEmailValue === defaultEmailPlaceholder) {
                setEmailErrorState(true, "O campo de e-mail é obrigatório.");
                if (!clientSideError) emailInput.focus();
                clientSideError = true;
            } else if (!checkEmailDomain(currentEmailValueLower)) {
                setEmailErrorState(true, "Por favor, use um e-mail corporativo com domínio válido.");
                if (!clientSideError) emailInput.focus();
                clientSideError = true;
            } else {
                setEmailErrorState(false); // Limpa erro de email se corrigido
            }

            if (passwordInput.value.trim() === '') {
                // Adicionar estilo de erro para senha se desejar, mas 'required' já bloqueia
                // passwordInput.classList.add('input-error');
                if (!clientSideError) passwordInput.focus(); // Foca na senha se o email estiver OK
                clientSideError = true;
            } else {
                // passwordInput.classList.remove('input-error');
            }

            if (clientSideError) {
                event.preventDefault();
                console.log("script.js: Submit do login bloqueado pelo cliente.");
                return;
            }
            console.log("script.js: Validação de cliente do login OK. Enviando para o servidor.");
        });
        console.log("script.js: Validação de e-mail e placeholder para login configurados.");
    } else if (loginForm) {
         console.warn("script.js: Formulário de login encontrado, mas um ou mais campos (email, password, emailErrorMessage) estão faltando.");
    }

});