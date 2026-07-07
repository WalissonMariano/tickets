<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chamados</title>
    @vite(['resources/css/layouts/menu.css'])
</head>
<body class="app-layout">
    <div class="app-shell">
        <aside class="app-sidebar" id="sidebar">
            <div class="app-sidebar-header">
                <div class="app-sidebar-logo">
                    <img
                        src="{{ asset('images/logo_tickets_menu.png') }}"
                        alt="Chamados.TI"
                        class="app-sidebar-logo-img"
                    >
                </div>
            </div>

            <nav class="app-sidebar-nav">
                <a href="{{ route('home') }}" class="app-nav-link is-active" data-frame-link target="content-frame">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    Home
                </a>
                <a href="{{ route('dashboard') }}" class="app-nav-link" data-frame-link target="content-frame">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                    Dashboard
                </a>
                <a href="#" class="app-nav-link" data-frame-link>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                    Tarefas
                </a>
                <a href="#" class="app-nav-link" data-frame-link>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h15M4.5 12h15m-15 5.25h15" />
                    </svg>
                    Cadastros
                </a>
            </nav>

            <div class="app-sidebar-footer">
                <div class="app-user-menu">
                    <button type="button" class="app-user-info" id="user-toggle" aria-expanded="false">
                        <div class="app-user-avatar">U</div>
                        <div class="app-user-details">
                            <strong>Usuário</strong>
                            <span>usuario@email.com</span>
                        </div>
                    </button>

                    <nav class="app-user-submenu" id="user-submenu" aria-label="Menu do usuário">
                        <p class="app-user-submenu-title">Minha conta</p>
                        <a href="#" class="app-user-submenu-link">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            Ver conta
                        </a>
                        <button type="button" class="app-user-submenu-link app-user-submenu-link--danger">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                            </svg>
                            Sair
                        </button>
                    </nav>
                </div>
            </div>
        </aside>

        <div class="app-sidebar-overlay" id="sidebar-overlay"></div>

        <main class="app-main">
            <header class="app-topbar">
                <button class="app-menu-toggle" id="menu-toggle" type="button" aria-label="Abrir menu">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <span class="app-topbar-title" id="topbar-title">Home</span>
            </header>

            <div class="app-content">
                <iframe
                    id="content-frame"
                    name="content-frame"
                    class="app-iframe"
                    src="{{ route('home') }}"
                    title="Conteúdo principal"
                ></iframe>
            </div>
        </main>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const menuToggle = document.getElementById('menu-toggle');
        const topbarTitle = document.getElementById('topbar-title');
        const navLinks = document.querySelectorAll('[data-frame-link]');

        function closeSidebar() {
            sidebar.classList.remove('is-open');
            overlay.classList.remove('is-visible');
        }

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('is-open');
            overlay.classList.toggle('is-visible');
        });

        overlay.addEventListener('click', closeSidebar);

        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                if (link.getAttribute('href') === '#') {
                    e.preventDefault();
                }

                navLinks.forEach(l => l.classList.remove('is-active'));
                link.classList.add('is-active');
                topbarTitle.textContent = link.textContent.trim();
                closeSidebar();
            });
        });

        const userToggle = document.getElementById('user-toggle');
        const userSubmenu = document.getElementById('user-submenu');

        function closeUserSubmenu() {
            userSubmenu.classList.remove('is-open');
            userToggle.setAttribute('aria-expanded', 'false');
        }

        userToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = userSubmenu.classList.toggle('is-open');
            userToggle.setAttribute('aria-expanded', String(isOpen));
        });

        userSubmenu.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        document.addEventListener('click', closeUserSubmenu);
    </script>
</body>
</html>
