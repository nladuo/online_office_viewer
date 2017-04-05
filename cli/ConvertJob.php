<?php

/**
 * Created by PhpStorm.
 * User: kalen
 * Date: 2017/4/4
 * Time: 下午4:07
 */
class ConvertJob
{
    public function perform()
    {
        $filename = $this->args["filename"];

        # Convert the pdf
        fwrite(STDOUT, "start converting ". $filename. "\n");
        shell_exec("unoconv -f pdf " . "../uploaded_files/".$filename);
        fwrite(STDOUT, "converted ". $filename. " finished\n");

        # remove the office file
        fwrite(STDOUT, "start removing ". $filename. "\n");
        shell_exec("rm -f " . "../uploaded_files/".$filename);
        fwrite(STDOUT, "removed ". $filename. " success\n");
    }
}