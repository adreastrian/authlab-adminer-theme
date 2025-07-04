<?php

class AdminerDarkSwitcher extends \Adminer\Plugin
{
    public function head($dark = null)
    {
        ?>
        <style>
        /* The main container to position the dropdown */
        .dropdown-container {
            margin-right: 1em;
        }
        
        /* The button that is always visible */
        .dropdown-trigger {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0;
            font-size: 20px;
            background-color: transparent;
            border: none;
            cursor: pointer;
            color: var(--color-text);
        }

        .dropdown-trigger svg {
            width: 20px;
            height: 20px;
        }

        /* The dropdown menu that is hidden by default */
        .theme-menu {
            position: absolute;
            width: 200px;
            background-color: var(--color-background);
            top: 40px;
            padding: 8px;
            z-index: 10;
            
            /* Hide the menu by default */
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;
        }
        
        /* Class added by JS to show the menu */
        .theme-menu.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        /* Styling for each button/menu item */
        .menu-item {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 8px 8px;
            font-size: 14px;
            background: none;
            border: none;
            text-align: left;
            cursor: pointer;
            color: var(--color-text);
        }
        
        .menu-item:hover,
        .menu-item.selected:hover {
            background-color: var(--color-table-row-checked);
        }

        .menu-item svg {
            margin-right: 12px;
        }
        
        .menu-item .menu-text {
            flex-grow: 1;
        }
        
        .menu-item .checkmark {
            visibility: hidden;
        }
        
        .menu-item.selected .checkmark {
            visibility: visible;
        }
        
        .menu-item.selected {
            background-color: var(--color-base-variant-3);
        }
        </style>
        
        <script <?php echo Adminer\nonce(); ?>%pcs-comment-end#* />
            (function() {
                const savedTheme = localStorage.getItem('adminer_dark_mode');

                if (savedTheme === 'light') {
                    document.documentElement.classList.add('light-theme');
                } else if (savedTheme === 'dark') {
                    document.documentElement.classList.remove('light-theme');
                } else {
                    document.documentElement.classList.toggle('light-theme', window.matchMedia('(prefers-color-scheme: light)').matches);
                }
            })()

            function getCurrentTheme() {
                const savedTheme = localStorage.getItem('adminer_dark_mode');
                return savedTheme || 'system';
            }

            document.addEventListener('DOMContentLoaded', function() {
                const dropdownContainer = document.querySelector('.dropdown-container');
                const triggerButton = document.getElementById('theme-trigger');
                const themeMenu = document.getElementById('theme-menu');
                const menuItems = document.querySelectorAll('.menu-item');

                function updateTrigger() {
                    const selectedItem = document.querySelector('.menu-item.selected');
                    if (selectedItem) {
                        const icon = selectedItem.querySelector('svg');
                        if (icon) {
                            triggerButton.innerHTML = '';
                            triggerButton.appendChild(icon.cloneNode(true));
                        }
                    }
                }

                triggerButton.addEventListener('click', (event) => {
                    event.stopPropagation();
                    themeMenu.classList.toggle('open');
                });

                menuItems.forEach(item => {
                    item.addEventListener('click', () => {
                        const currentlySelected = document.querySelector('.menu-item.selected');
                        if (currentlySelected) {
                            currentlySelected.classList.remove('selected');
                        }
                        
                        item.classList.add('selected');
                        
                        updateTrigger();
                        
                        themeMenu.classList.remove('open');
                        
                        applyTheme(item.dataset.themeValue);
                    });
                });

                window.addEventListener('click', (event) => {
                    if (themeMenu.classList.contains('open') && !dropdownContainer.contains(event.target)) {
                        themeMenu.classList.remove('open');
                    }
                });

                updateTrigger();

                const htmlElement = document.documentElement;
                const lightModeMediaQuery = window.matchMedia('(prefers-color-scheme: light)');

                lightModeMediaQuery.addEventListener('change', (e) => {
                    const savedTheme = localStorage.getItem('adminer_dark_mode');
                    if (!savedTheme) {
                        applyTheme('system');
                    }
                });

                function applyInitialTheme() {
                    const savedTheme = localStorage.getItem('adminer_dark_mode');
                    applyTheme(savedTheme || 'system');
                }

                /**
                 * Applies a theme and saves the choice to localStorage.
                 * @param {string} theme The theme to apply: 'light', 'dark', or 'system'.
                 */
                function applyTheme(theme) {
                    if (theme === 'light') {
                        htmlElement.classList.add('light-theme');
                    } else if (theme === 'dark') {
                        htmlElement.classList.remove('light-theme');
                    } else {
                        htmlElement.classList.toggle('light-theme', lightModeMediaQuery.matches);
                    }

                    if (theme === 'system') {
                        localStorage.removeItem('adminer_dark_mode');
                    } else {
                        localStorage.setItem('adminer_dark_mode', theme);
                    }

                    const currentlySelected = document.querySelector('.menu-item.selected');
                    if (currentlySelected) {
                        currentlySelected.classList.remove('selected');
                    }

                    const activeMenuItem = document.querySelector(`.menu-item[data-theme-value="${theme}"]`);
                    if (activeMenuItem) {
                        activeMenuItem.classList.add('selected');
                    }

                    updateTrigger();
                }

                applyInitialTheme();

                (function moveDarkSwitcherToTop() {
                    const customNavMenu = document.querySelector('.custom-nav-menu');
                    const logout = document.querySelector('.logout').parentNode;
                    
                    customNavMenu.appendChild(logout);
                })();
            });
        </script>
<?php
    }

    public function navigation($missing)
    {
        $this->renderThemeDropdown();
    }

    public function renderThemeDropdown()
    {
        echo <<<HTML
        <div class="custom-nav-menu">
            <div class="dropdown-container dark-switcher">
                <button class="dropdown-trigger" id="theme-trigger">
                    </button>

                <div class="theme-menu" id="theme-menu">
                    <button class="menu-item" data-theme-value="light">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="none" class=""><path d="M10 14.5C8.80653 14.5 7.66193 14.0259 6.81802 13.182C5.97411 12.3381 5.5 11.1935 5.5 10C5.5 8.80653 5.97411 7.66193 6.81802 6.81802C7.66193 5.97411 8.80653 5.5 10 5.5C11.1935 5.5 12.3381 5.97411 13.182 6.81802C14.0259 7.66193 14.5 8.80653 14.5 10C14.5 11.1935 14.0259 12.3381 13.182 13.182C12.3381 14.0259 11.1935 14.5 10 14.5ZM10 13C10.7956 13 11.5587 12.6839 12.1213 12.1213C12.6839 11.5587 13 10.7956 13 10C13 9.20435 12.6839 8.44129 12.1213 7.87868C11.5587 7.31607 10.7956 7 10 7C9.20435 7 8.44129 7.31607 7.87868 7.87868C7.31607 8.44129 7 9.20435 7 10C7 10.7956 7.31607 11.5587 7.87868 12.1213C8.44129 12.6839 9.20435 13 10 13ZM9.25 1.75H10.75V4H9.25V1.75ZM9.25 16H10.75V18.25H9.25V16ZM3.63625 4.69675L4.69675 3.63625L6.2875 5.227L5.227 6.2875L3.63625 4.6975V4.69675ZM13.7125 14.773L14.773 13.7125L16.3638 15.3032L15.3032 16.3638L13.7125 14.773ZM15.3032 3.6355L16.3638 4.69675L14.773 6.2875L13.7125 5.227L15.3032 3.63625V3.6355ZM5.227 13.7125L6.2875 14.773L4.69675 16.3638L3.63625 15.3032L5.227 13.7125ZM18.25 9.25V10.75H16V9.25H18.25ZM4 9.25V10.75H1.75V9.25H4Z" fill="currentColor"></path></svg>

                    <span class="menu-text">Light</span>
                    <span class="checkmark"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                    </button>

                    <button class="menu-item" data-theme-value="dark">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="none" class=""><path d="M8.5 6.25C8.49985 7.29298 8.81035 8.31237 9.39192 9.17816C9.97348 10.0439 10.7997 10.7169 11.7653 11.1112C12.7309 11.5055 13.7921 11.6032 14.8134 11.3919C15.8348 11.1807 16.7701 10.67 17.5 9.925V10C17.5 14.1423 14.1423 17.5 10 17.5C5.85775 17.5 2.5 14.1423 2.5 10C2.5 5.85775 5.85775 2.5 10 2.5H10.075C9.57553 2.98834 9.17886 3.57172 8.90836 4.21576C8.63786 4.8598 8.49902 5.55146 8.5 6.25ZM4 10C3.99945 11.3387 4.44665 12.6392 5.27042 13.6945C6.09419 14.7497 7.24723 15.4992 8.54606 15.8236C9.84489 16.148 11.2149 16.0287 12.4381 15.4847C13.6614 14.9407 14.6675 14.0033 15.2965 12.8215C14.1771 13.0852 13.0088 13.0586 11.9026 12.744C10.7964 12.4295 9.78888 11.8376 8.97566 11.0243C8.16244 10.2111 7.57048 9.20361 7.25596 8.09738C6.94144 6.99116 6.91477 5.82292 7.1785 4.7035C6.21818 5.2151 5.41509 5.97825 4.85519 6.91123C4.2953 7.84422 3.99968 8.91191 4 10Z" fill="currentColor"></path></svg>
                    <span class="menu-text">Dark</span>
                    <span class="checkmark"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                    </button>
                    
                    <button class="menu-item selected" data-theme-value="system">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class=""><path d="M6.875 4.375V6.25H4.375V7.5H6.875V9.375H8.125V4.375H6.875ZM9.375 7.5H15.625V6.25H9.375V7.5ZM13.125 10.625V12.5H15.625V13.75H13.125V15.625H11.875V10.625H13.125ZM10.625 13.75H4.375V12.5H10.625V13.75Z" fill="currentColor"></path></svg>
                    <span class="menu-text">System</span>
                    <span class="checkmark"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                    </button>
                </div>
            </div>
        </div>
        HTML;
    }
}
