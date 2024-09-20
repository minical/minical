<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
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

form {
    display: flex;
    flex-direction: column;
    align-items: center;
}

label {
    font-size: 14px;
    margin-bottom: 10px;
}

input[type="text"] {
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin-bottom: 20px;
    width: calc(100% - 24px); /* Full width minus padding */
    box-sizing: border-box;
}

button {
    padding: 10px 20px;
    font-size: 16px;
    background-color: #007bff;
    color: #ffffff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.forget_ga_app{
    background-color: #ffc107;
    font-size: small;
}

a {
    padding: 10px 20px;
    font-size: 16px;
    background-color: #007bff;
    color: #ffffff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
}

button:hover {
    background-color: #0056b3;
}

#secret {
    font-weight: bold;
    color: #333;
}

.otp-input {
    display: flex;
    justify-content: space-between;
}
.otp-input input {
    width: 50px;
    height: 50px;
    text-align: center;
    font-size: 24px;
    margin: 0 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

</style>
<input type="hidden" name="project_url" id="project_url" value="<?php echo base_url(); ?>">
<div class="container">
	<?php if(isset($secure_data['enabled']) && !$secure_data['enabled']): ?>
	    <h1>Two-Factor Authentication (2FA)</h1>
	    <p>Scan the QR code below with your Google Authenticator app:</p>
	    <div class="qr-code">
	        <img src="<?php echo $secure_data['qr_code_url']; ?>" alt="QR Code">
            <input type="hidden" name="qr_code_url" id="qr_code_url" value="<?php echo $secure_data['qr_code_url']; ?>">
            <input type="hidden" name="secret" id="secret_code" value="<?php echo $secure_data['secret_code']; ?>">
	    </div>

	    <p>If you can't scan the QR code, use this code: <span id="secret"><?php echo $secure_data['secret_code']; ?></span></p>
	<?php endif; ?>

    <form action="" method="post" class="otp_verify_form">


        <label for="otp">Enter the code from your Google Authenticator app:</label>
        <div class="otp-input">
            <input type="text" maxlength="1" required>
            <input type="text" maxlength="1" required>
            <input type="text" maxlength="1" required>
            <input type="text" maxlength="1" required>
            <input type="text" maxlength="1" required>
            <input type="text" maxlength="1" required>
        </div>

        <hr>



        
        <!-- <input id="otp" name="otp" type="text" maxlength="6" /> -->
        <a href="javascript:" class="verify_otp">Verify</a>
        <hr>
        
    </form>

<?php if(isset($secure_data['enabled']) && $secure_data['enabled']): ?>
<button type="button" class="btn btn-warning btn-sm forget_ga_app" style="float: right;display: none;">Lost GA App Code</button>
<button type="button" class="btn reload_page" style="float: right; display: none;"></button>
<?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
<script type="text/javascript" src="<?php echo base_url() . auto_version('js/helpers.js');?>"></script>
<script type="text/javascript" src="<?php echo base_url() . auto_version('js/company_security.js');?>"></script>
