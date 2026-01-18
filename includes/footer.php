<?php 
$date = date('Y');
?>

<footer style="text-align:center; padding:30px; background: linear-gradient(135deg, #1a3a52 0%, #2d5a7b 100%); color:#fff; font-family:'Poppins', Arial, sans-serif; font-size:14px; margin-top: 50px;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-bottom: 30px; text-align: left;">
            <div>
                <h5 style="color: #fff; margin-bottom: 15px;"><i class="fas fa-car"></i> VehiCare</h5>
                <p>Your trusted vehicle maintenance and repair service center.</p>
            </div>
            <div>
                <h5 style="color: #fff; margin-bottom: 15px;">Quick Links</h5>
                <ul style="list-style: none; padding: 0;">
                    <li><a href="#" style="color: #bbb; text-decoration: none;">Home</a></li>
                    <li><a href="#" style="color: #bbb; text-decoration: none;">Services</a></li>
                    <li><a href="#" style="color: #bbb; text-decoration: none;">About Us</a></li>
                </ul>
            </div>
            <div>
                <h5 style="color: #fff; margin-bottom: 15px;">Contact Info</h5>
                <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                <p><i class="fas fa-envelope"></i> info@vehicare.com</p>
            </div>
        </div>
        <hr style="border-color: rgba(255,255,255,0.2);">
        <p>© <?php echo $date; ?> <strong>VehiCare</strong>. All Rights Reserved. | Powered by VehiCare System</p>
    </div>
</footer>

</body>
</html>
