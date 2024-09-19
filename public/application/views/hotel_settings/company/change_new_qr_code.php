<style type="text/css">
	/* styles.css */

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

button:hover {
    background-color: #0056b3;
}

#secret {
    font-weight: bold;
    color: #333;
}

/*#new_otp {
  padding-left: 15px;
  letter-spacing: 42px;
  border: 0;
  background-image: linear-gradient(to left, black 70%, rgba(255, 255, 255, 0) 0%);
  background-position: bottom;
  background-size: 50px 1px;
  background-repeat: repeat-x;
  background-position-x: 35px;
  width: 300px;
  outline : none;
}
*/

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
<div>
    <a href="javascript:" class="btn btn-primary back_to_security">Back</a>
</div>
<div class="container">
	
	    <h1>Two-Factor Authentication (2FA)</h1>
	    <p>Scan the QR code below with your Google Authenticator app:</p>
	    <div class="qr-code">
	        <img src="<?php echo $secure_data['qr_code_url']; ?>" alt="QR Code">
	    </div>

	    <p>If you can't scan the QR code, use this code: <span id="secret"><?php echo $secure_data['secret_code']; ?></span></p>
	

    <form action="" method="post">
        <label for="otp">Enter the code from your Google Authenticator app:</label>
        <!-- <input id="new_otp" name="new_otp" type="text" maxlength="6" /> -->


        <div class="otp-input">
            <input type="text" maxlength="1" required>
            <input type="text" maxlength="1" required>
            <input type="text" maxlength="1" required>
            <input type="text" maxlength="1" required>
            <input type="text" maxlength="1" required>
            <input type="text" maxlength="1" required>
        </div>


        <input id="secret_code" name="secret_code" type="hidden" value="<?php echo $secure_data['secret_code']; ?>"/>
        <input id="qr_code_url" name="qr_code_url" type="hidden" value="<?php echo $secure_data['qr_code_url']; ?>"/>
        <input id="security_name" name="security_name" type="hidden" value="<?php echo $security_name; ?>"/>
        <a style="margin-top: 15px;" href="javascript:" class="verify_new_qr_otp btn btn-primary">Verify</a>
    </form>

</div>

<script type="text/javascript">
    const inputs = document.querySelectorAll('.otp-input input');
    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            if (e.target.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && index > 0 && !e.target.value) {
                inputs[index - 1].focus();
            }
        });
    });

    $('body').on('click','.verify_new_qr_otp',function(){

        // var otp = $('#new_otp').val();
        console.log('inputs',inputs);
        let otp = '';
        inputs.forEach(input => {
            otp += input.value;
        });

        console.log('inputs',otp);

        var secret = $('#secret_code').val();
        var qr_code_url = $('#qr_code_url').val();
        var security_name = $('#security_name').val();

        $.ajax({
            type: "POST",
            url: getBaseURL() + 'settings/company_security/verify_new_otp',
            dataType: 'json',
            data: {
                    otp: otp,
                    secret: secret,
                    qr_code_url: qr_code_url,
                    security_name: security_name,
                    via: 'company_security_first_time'
                },
            success: function(resp){
                console.log('resp',resp);
                if(resp.success){
                    location.reload();
                } else {
                    alert(resp.error_msg);
                }
            }
        });
    });
</script>