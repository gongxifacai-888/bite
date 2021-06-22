<?php


namespace App\Uploader;


use Illuminate\Support\Facades\Validator;

class Local extends Uploader
{

    /**
     * @param \Illuminate\Http\UploadedFile $file
     *
     * @return array|void
     * @throws \Exception
     */
    public function upload($file)
    {
        parent::upload($file);

        $filename = $this->filename;
        $ext = $this->ext;

        $path = "/uploads/" . date('Ymd') . '/';
        $full_path = public_path($path);
        file_exists($path) || @mkdir($full_path, 0777, true);
        $filename = md5($filename . uniqid()) . ".{$ext}";
        $file->move($full_path, $filename);

        $this->path = $path . $filename;
        $this->url = url($this->path);

        return $this->formatMsg();
    }
}
