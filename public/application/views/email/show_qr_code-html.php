<style type="text/css">
	/* styles.css */

body {
    font-family: Arial, sans-serif;
    background-color: #f5f5f5;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

.container {
    background-color: #ffffff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    text-align: center;
    max-width: 400px;
    width: 100%;
}

h1 {
    font-size: 24px;
    margin-bottom: 20px;
}

.qr-code img {
    width: 200px;
    height: 200px;
    margin-bottom: 20px;
}

label {
    font-size: 14px;
    margin-bottom: 10px;
}


#secret {
    font-weight: bold;
    color: #333;
}



</style>
<div class="container" style="text-align: center;">
	
	    <h1>Two-Factor Authentication (2FA)</h1>
	    <p>Scan the QR code below with your Google Authenticator app:</p>
	    <div class="qr-code">
	        <img src="<?php echo $secure_data['qr_code_url']; ?>" alt="QR Code">
            <input type="hidden" name="qr_code_url" id="qr_code_url" value="<?php echo $secure_data['qr_code_url']; ?>">
            <input type="hidden" name="secret" id="secret_code" value="<?php echo $secure_data['secret_code']; ?>">
	    </div>

	    <p>If you can't scan the QR code, use this code: <span id="secret"><?php echo $secure_data['secret_code']; ?></span></p>
</div>