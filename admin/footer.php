            </div>
        </main>
    </div>

    <script>
        let sidebarCollapsed = document.cookie.includes('sidebarCollapsed=true');

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            const texts = document.querySelectorAll('.sidebar-text');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            
            sidebarCollapsed = !sidebarCollapsed;
            
            // Sauvegarder dans un cookie
            document.cookie = `sidebarCollapsed=${sidebarCollapsed}; path=/; max-age=2592000`; // 30 jours
            
            if (sidebarCollapsed) {
                sidebar.style.width = '5rem';
                toggle.classList.add('rotate-180');
                sidebarTexts.forEach(t => {
                    t.classList.add('w-0', 'opacity-0');
                    t.classList.remove('w-auto', 'opacity-100');
                });
            } else {
                sidebar.style.width = '16rem';
                toggle.classList.remove('rotate-180');
                sidebarTexts.forEach(t => {
                    t.classList.remove('w-0', 'opacity-0');
                    t.classList.add('w-auto', 'opacity-100');
                });
            }
        }

        // Initialisation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            // Animation d'entrée pour les cartes
            const cards = document.querySelectorAll('.group');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
