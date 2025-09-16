<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'components/_base.php';

$_title="Profile Update with Webcam";
// ----------------------------------------------------------------------------


// Ensure the user is authenticated
auth("Member");


// Initialize errors array
$_err = [];


if (is_get()) {
    // Fetch the current user's data
    $stm = $_db->prepare('SELECT * FROM user WHERE id = ?');
    $stm->execute([$_user->id]);  // Use current user's ID from session
    $u = $stm->fetch();


    // If user not found, redirect to homepage
    if (!$u) {
        redirect('/');
    }


    // Pre-fill form data with current values
    extract((array)$u); // Extract data to populate the form
    $_SESSION['image'] = $u->image; // Store current image in session
}


if (is_post()) {
    $name      = req('name');
    $gender    = req('gender');
    $dob       = req('dob');
    $email     = req('email');
    $telephone = req('telephone');
    $address   = req('address');
    $image     = $_SESSION['image']; // Get stored image from session
    $f         = get_file('image');  // Fetch uploaded image file
    $webcamImage = req('webcamImage'); // The Base64 image from the webcam


    // Validate fields
    if ($name == '') {
        $_err['name'] = 'Name is required';
    } elseif (strlen($name) > 100) {
        $_err['name'] = 'Maximum length is 100 characters';
    }


    if ($gender == '') {
        $_err['gender'] = 'Gender is required';
    } elseif (!in_array($gender, ['M', 'F'])) {
        $_err['gender'] = 'Invalid gender';
    }


    if ($dob == '') {
        $_err['dob'] = 'Date of Birth is required';
    }


    if ($email == '') {
        $_err['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_err['email'] = 'Invalid email format';
    }


    if ($telephone == '') {
        $_err['telephone'] = 'Telephone is required';
    }elseif (!preg_match('/^\d{3}-\d{8}$/', $telephone)) {
        $_err['telephone'] = 'Telephone number must be in number and in the format xxx-xxxxxxxx';
    }


    if ($address == '') {
        $_err['address'] = 'Address is required';
    }


    if (!empty($webcamImage)) {
        // Decode Base64 string and save it as a file
        $image_data = explode(',', $webcamImage); // Separate Base64 header from data
        $image_base64 = base64_decode($image_data[1]); // Decode Base64 string
        $image_name = uniqid() . '.png'; // Generate unique image name
        $image_path = 'user_img/' . $image_name; // Specify the image path
        
        // Save the image on the server and check if the operation is successful
        if (file_put_contents($image_path, $image_base64)) {
            // Set the image path to store in the database
            $image = $image_name;
        } else {
            // Handle error if the image could not be saved
            $_err['image'] = 'Failed to save webcam image. Please try again.';
        }
    }
    
     


    // Validate: image (only if a file is selected)
    if ($f) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['image'] = 'Must be an image';
        } elseif ($f->size > 1 * 1024 * 1024) {
            $_err['image'] = 'Maximum file size is 1MB';
        }
    }


    // Update profile if no validation errors
    if (empty($_err)) {
        // If a new image is uploaded, delete the old image and save the new one
        if ($f) {
            // Use relative path to 'user_img' folder
            unlink("user_img/$image"); // Delete old image
           
            // Save the new image in the correct folder
            $image = save_photo($f, 'user_img'); // Save new image
        }


        // Prepare and execute the update query
        $stm = $_db->prepare('
            UPDATE user
            SET name = ?, gender = ?, dob = ?, email = ?, telephone = ?, address = ?, image = ?
            WHERE id = ?');
        $stm->execute([$name, $gender, $dob, $email, $telephone, $address, $image, $_user->id]); // Update using session user ID


        if ($stm->rowCount()) {
            // Update the global session with new data
            $_user->name = $name;
            $_user->gender = $gender;
            $_user->dob = $dob;
            $_user->email = $email;
            $_user->telephone = $telephone;
            $_user->address = $address;
            $_user->image = $image;


            // Set success message and redirect to the profile page
            temp('info', 'Profile updated successfully');
            redirect('/profile.php');
        } else {
            temp('error', 'Failed to update the profile');
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?= $_title?></title>

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/member_profile_update.css">
</head>
<body>


<?php include 'components/user_header.php'; ?>


<section class="profile-container">


<!-- HTML Form for Profile Update -->
<form method="post" class="form" enctype="multipart/form-data">


    <!-- User ID (Non-editable) -->


    <!-- Profile Picture with File Upload and Webcam -->
    <label for="image"><h2>Profile Picture</h2></label>
    <label class="upload" tabindex="0">
       <?= html_file('image', 'image/*', 'hidden', 'onchange="resizeUploadedImage(this)"') ?>
       <img id="imagePreview" src="user_img/<?= encode($image ?? '') ?>" alt="Profile Image">
    </label>
    <?= err('image') ?>


    <!-- Webcam Capture Section -->
    <div>
        <video id="vid" autoplay muted style="display:none;"></video> <!-- Video stream (hidden initially) -->
        <canvas id="canvas" style="display:none;"></canvas> <!-- Canvas for capturing the image -->
    </div>


    <br/>
    <button type="button" id="startWebcam">Open Webcam</button>
    <button type="button" id="capturePhoto" style="display:none;">Capture Photo</button>
    <button type="button" id="stopWebcam" style="display:none;">Stop Webcam</button>
    <input type="hidden" name="webcamImage" id="webcamImage">


    <!-- Name -->
    <label for="name"><h2>Name</h2></label>
    <?= html_text('name', 'maxlength="100"', encode($name ?? '')) ?>
    <?= err('name') ?>
   
    <!-- Gender -->
    <label><h2>Gender</h2></label>
    <?= html_radios('gender', ['M' => 'M', 'F' => 'F'], encode($gender ?? '')) ?>
    <?= err('gender') ?>
   
    <!-- Date of Birth -->
    <label for="dob"><h2>Date of Birth</h2></label>
    <input type="date" name="dob" value="<?= encode($dob ?? '') ?>">
    <?= err('dob') ?>
   
    <!-- Email -->
    <label for="email"><h2>Email</h2></label>
    <?= html_text('email', '', encode($email ?? '')) ?>
    <?= err('email') ?>
   
    <!-- Telephone -->
    <label for="telephone"><h2>Telephone</h2></label>
    <?= html_text('telephone', '', encode($telephone ?? '')) ?>
    <?= err('telephone') ?>
   
    <!-- Address -->
    <label for="address"><h2>Address</h2></label>
    <textarea name="address"><?= encode($address ?? '') ?></textarea>
    <?= err('address') ?>
   
    <!-- Submit and Reset Buttons -->
    <section>
        <button type="submit">Submit</button>
        <button type="reset">Reset</button>
        <button type="button" onclick="window.location.href='/profile.php'">Back</button>
    </section>


</form>
</section>






<script>
    document.querySelector('input[name="image"]').addEventListener('change', function (event) {
    const [file] = event.target.files;
    if (file) {
        document.getElementById('imagePreview').src = URL.createObjectURL(file);
    }
});
    // Resize images to fixed size (200x200)
    const FIXED_WIDTH = 200;
    const FIXED_HEIGHT = 200;


    // Function to resize uploaded images
    function resizeUploadedImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.src = e.target.result;
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    canvas.width = FIXED_WIDTH;
                    canvas.height = FIXED_HEIGHT;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, FIXED_WIDTH, FIXED_HEIGHT);
                    const resizedDataURL = canvas.toDataURL('image/png');
                    document.getElementById('imagePreview').src = resizedDataURL;
                    document.getElementById('webcamImage').value = resizedDataURL;
                   
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }


   // Webcam functionality
document.addEventListener("DOMContentLoaded", () => {
    let video = document.getElementById("vid");
    let canvas = document.getElementById("canvas");
    let startWebcamBtn = document.getElementById("startWebcam");
    let capturePhotoBtn = document.getElementById("capturePhoto");
    let webcamImageField = document.getElementById("webcamImage");
    let stopWebcamBtn = document.getElementById('stopWebcam');
    let imagePreview = document.getElementById("imagePreview");
    let mediaDevices = navigator.mediaDevices;
    let webcamStream; // Declare a variable to store the stream

    // Start the webcam stream when the button is clicked
    startWebcamBtn.addEventListener("click", () => {
        mediaDevices.getUserMedia({
            video: true
        }).then((stream) => {
            webcamStream = stream;  // Store the stream in the variable
            video.srcObject = stream;
            video.style.display = "block";
            capturePhotoBtn.style.display = "inline";
            stopWebcamBtn.style.display = 'block';
            startWebcamBtn.style.display = "none"; // Hide "Open Webcam" button
        }).catch((err) => {
            alert("Webcam access denied: " + err);
        });
    });

    // Stop the webcam when the "Stop Webcam" button is clicked
    stopWebcamBtn.addEventListener('click', () => {
        if (webcamStream) {
            webcamStream.getTracks().forEach(track => track.stop()); // Stop the webcam
        }
        video.style.display = 'none';
        capturePhotoBtn.style.display = 'none';
        stopWebcamBtn.style.display = 'none';
        startWebcamBtn.style.display = 'inline';
    });

    // Capture the photo and resize it to fixed dimensions
    capturePhotoBtn.addEventListener("click", () => {
        let context = canvas.getContext('2d');
        canvas.width = FIXED_WIDTH;
        canvas.height = FIXED_HEIGHT;
        context.drawImage(video, 0, 0, FIXED_WIDTH, FIXED_HEIGHT);

        let imageDataURL = canvas.toDataURL('image/png');
        webcamImageField.value = imageDataURL; // Set the hidden field value

        // Show the resized image in the preview
        imagePreview.src = imageDataURL;

        // Stop the video stream
        if (webcamStream) {
            webcamStream.getTracks().forEach(track => track.stop());
        }
        video.style.display = "none";
        capturePhotoBtn.style.display = "none";
        startWebcamBtn.style.display = "inline";
    });
});

</script>


<?php include 'components/footer.php'; ?>


<script src="js/script.js"></script>
</body>
</html>







