<?php
require 'config/database.php';


//get form data if submit button was clicked
if(isset($_POST['submit'])) {

    $author_id = $_SESSION['user-id'];
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $body = filter_var($_POST['body'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $category_id = filter_var($_POST['category'], FILTER_SANITIZE_NUMBER_INT);
    $is_featured = filter_var($_POST['is_featured'], FILTER_SANITIZE_NUMBER_INT);
    $thumbnail = $_FILES['thumbnail'];

    //set is_featured to 0 if unchecked
    $is_featured = $is_featured == 1 ?: 0;

    //validate form data
    if(!$title) {
        $_SESSION['add-post'] = "Enter post Title";
    }
    elseif (!$category_id){
        $_SESSION['add-post'] = "Select post Category";
    }
    elseif (!$body){
        $_SESSION['add-post'] = "Enter post body";
    }
    elseif (!$thumbnail['name']){
        $_SESSION['add-post'] = "Choose post Thumbnail";
    }
    else {
        //WORK ON THUMBNAIL
        //rename the image
        $time = time(); // make each image name unique
        $thumbnail_name = $time . $thumbnail['name'];
        $thumbnail_tmp_name = $thumbnail['tmp_name'];
        $thumbnail_destination_path = '../thumbnail/' . $thumbnail_name;

        // make sure the file is really an image
        $allowed_files = ['png', 'jpg', 'jpeg'];
        $extension = explode('.', $thumbnail_name);
        $extension = end($extension);
        if(in_array($extension, $allowed_files)) {
            //make sure image is not too big. (2mb+)
            if($thumbnail['size'] < 2000000){
                //upload thumbnail
                move_uploaded_file($thumbnail_tmp_name, $thumbnail_destination_path);
            }
            else {
                $_SESSION['add-post'] = "File size too bog, should be less than 2mb";
            }
        }
        else {
            $_SESSION['add-post'] = "Files should be png, jpg or jpeg";
        }
    }

    // redirect back to add-post page if there was any problem
    if(isset($_SESSION['add-post'])) {
        //pass form data back to add-post page
        $_SESSION['add-post-data'] = $_POST;
        header('location: ' . ROOT_URL . '/admin/add-post.php');
        die();
    }
    else {
        
        //set is_featured of all the post to 0 if is_featured for this post is 1
        if($is_featured == 1) {
            $zero_all_is_featured_query = "UPDATE posts SET is_featured=0";
            $zero_all_is_featured_result = mysqli_query($connection, $zero_all_is_featured_query);
        }

        //insert post into posts table
        $query = "INSERT INTO posts SET title='$title', body='$body', thumbnail='$thumbnail_name', category_id='$category_id', author_id='$author_id', is_featured='$is_featured'";

        $insert_post_results = mysqli_query($connection, $query);

        if(!mysqli_errno($connection)) {
            //redirect to login page
            $_SESSION['add-post-success'] = "New post added successfully";
            header('location: ' . ROOT_URL . 'admin/index.php');
            die();
        }
    }
}

header('location: ' . ROOT_URL . 'admin/add-post.php');
die();