            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update time
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                timeElement.textContent = `${hours}:${minutes}`;
            }
        }
        updateTime();
        setInterval(updateTime, 60000);
    </script>
</body>
</html>
