<?php
// app/Views/partials/footer.php
if (!defined('BASE_URL')) exit; 
?>
    </main>
    <script>
        // Lógica de Responsividade da Sidebar
        $(document).ready(function() {
            const sidebar = $('#sidebar');
            const menuToggle = $('#menu-toggle');
            const overlay = $('.sidebar-overlay');
            const mainContent = $('.main-content');
            const navbarDashboard = $('.navbar-dashboard');
            
            // Variável para armazenar o estado do sidebar em desktop (se for implementado collapse)
            let isSidebarOpen = window.innerWidth >= 993; 

            function toggleSidebar() {
                sidebar.toggleClass('active');
                overlay.toggleClass('active');
            }

            // Ação do botão Hamburger
            menuToggle.on('click', function() {
                toggleSidebar();
            });

            // Ação do overlay (clicar fora fecha em mobile)
            overlay.on('click', function() {
                toggleSidebar();
            });

            // Ajuste do layout na mudança de tamanho (desktop vs. mobile)
            function adjustLayout() {
                if (window.innerWidth >= 993) {
                    // Desktop: Navbar e Main Content se ajustam se a sidebar estiver visível (CSS padrão)
                    sidebar.addClass('active');
                    overlay.removeClass('active');
                    // Aqui você implementaria a lógica de colapsar/expandir em desktop se fosse um requisito.
                } else {
                    // Mobile: Sidebar começa escondida
                    sidebar.removeClass('active');
                    overlay.removeClass('active');
                }
            }

            $(window).on('resize', adjustLayout);
            adjustLayout(); // Chama ao carregar
        });
    </script>
</body>
</html>