<?php

date_default_timezone_set('GMT');
require_once __DIR__."/../vendor/autoload.php";
require_once "./utils/Uploader.php";
require_once "./utils/uuid.php";
require_once "config.php";

/**
 * JSON Response
 * @param $code
 * @param $msg
 * @param $data
 */
function json_response($code, $msg, $data) {
    echo json_encode([
        'code' => $code,
        'msg' => $msg,
        'data' => $data
    ]);
    exit(0);
}

/**
 * Add the ConvertJob to redis and wait worker finishing conversion
 * @param $filename
 * @return bool
 */
function wait_conversion($filename) {
    Resque::setBackend(REDIS_BACKEND);

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

// 1. Check the extension of upload file.
$allowed_exts = ["doc", "docx", "ppt", "pptx", "xls", "xlsx", "odt"];
$upload_dir = '../uploaded_files/';
$uploader = new FileUpload('uploadfile');

if (!in_array($uploader->getExtension(), $allowed_exts)) {
    json_response(1, "the extension " . $uploader->getExtension() . " is not supported", "");
}

// 2. Save the upload file to ../uploaded_files
$uuid = create_uuid();
$pdf_name = $uuid . ".pdf";
$uploader->newFileName = $uuid . "." . $uploader->getExtension();

$result = $uploader->handleUpload($upload_dir);

if (!$result) {
    json_response(1, $uploader->getErrorMsg(), "");
}

// 3. Convert the office file to pdf
$is_success = wait_conversion($uploader->newFileName);

// 4. Return the result
if ($is_success) {
    $href = "/uploaded_files/" . $pdf_name;
    json_response(0, "success", ["href" => $href]);
} else {
    json_response(1, "conversion failed", "");
}
