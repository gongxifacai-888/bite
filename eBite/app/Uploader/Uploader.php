<?php


namespace App\Uploader;


use Illuminate\Support\Facades\Validator;
use App\Exceptions\ThrowException;

abstract class Uploader
{
    protected $filename;

    protected $ext;

    protected $size;

    protected $origin;

    protected $domain;

    protected $url;

    protected $path;

    public function __construct()
    {
        $this->config();
    }

    public function config()
    {

    }

    /**
     * @param $class
     *
     * @return static
     */
    public static function newInstance($class)
    {
        return new $class();
    }

    /**
     * @param \Illuminate\Http\UploadedFile $file
     *
     * @return $this
     */
    public function upload($file)
    {
        $validator = Validator::make(request()->all(), [
            'file' => 'required|file'
        ]);

        if ($validator->fails()) {
            throw new ThrowException($validator->errors()->first());
        }

        $this->filename = $file->getFilename();
        $this->origin = $file->getClientOriginalName();
        $this->ext = $file->getClientOriginalExtension();
        $this->size = $file->getSize();
        $this->domain = request()->getHost();

        $ext_array = ['jpg', 'jpeg', 'png', 'apk', 'ipa'];
        if (!in_array(strtolower($this->ext), $ext_array)) {
            throw new ThrowException('上传的文件类型不被允许' . $this->ext);
        }

        return $this;
    }

    /**
     * @param \Illuminate\Http\UploadedFile $file
     *
     * @return array
     */
    public function formatMsg()
    {
        return [
            'original' => $this->origin,
            'ext' => $this->ext,
            'size' => $this->size,
            'domain' => $this->domain,
            'url' => $this->url,
            'path' => $this->path,
        ];
    }
}
