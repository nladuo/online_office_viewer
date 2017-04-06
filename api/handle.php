<?php

date_default_timezone_set('GMT');
require_once __DIR__."/../vendor/autoload.php";
require_once "./utils/Uploader.php";
require_once "./utils/uuid.php";


function response($code, $msg, $data) {
    echo json_encode([
        'code' => $code,
        'msg' => $msg,
        'data' => $data
    ]);
    exit(0);
}

function wait_convert($filename) {
    Resque::setBackend('127.0.0.1:6379');

    $args = array(
        'filename' => $filename
    );

    $jobId = Resque::enqueue('default', "ConvertJob", $args, true);

    $status = new Resque_Job_Status($jobId);
    if(!$status->isTracking()) {
        return false;
    }

    do {
        $code = $status->get();
        sleep(1);
    }while($code != 4 && $code != 3);
    return $code == 4;
}

$allowed_exts = ["doc", "docx", "ppt", "pptx", "xls", "xlsx", "odt"];

$upload_dir = '../uploaded_files/';

$uploader = new FileUpload('uploadfile');

if (!in_array($uploader->getExtension(), $allowed_exts)) {
    response(1, "the extension " . $uploader->getExtension() . " is not supported", "");
}

$uuid = create_uuid();
$pdf_name = $uuid . ".pdf";
$uploader->newFileName = $uuid . "." . $uploader->getExtension();

// Handle the upload
$result = $uploader->handleUpload($upload_dir);

if (!$result) {
    response(1, $uploader->getErrorMsg(), "");
}

// convert the file
$is_success = wait_convert($uploader->newFileName);

if ($is_success) {
    $href = "/uploaded_files/" . $pdf_name;
    response(0, "success", ["href" => $href]);
} else {
    response(1, "convert failed", "");
}
