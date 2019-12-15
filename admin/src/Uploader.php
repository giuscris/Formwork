<?php

namespace Formwork\Admin;

use Formwork\Admin\Exceptions\TranslatedException;
use Formwork\Core\Formwork;
use Formwork\Utils\FileSystem;
use Formwork\Utils\HTTPRequest;
use Formwork\Utils\MimeType;

class Uploader
{
    /**
     * Human-readable Uploader error messages
     *
     * @var array
     */
    protected const ERROR_MESSAGES = [
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload'
    ];

    /**
     * Uploader errors language strings
     *
     * @var array
     */
    protected const ERROR_LANGUAGE_STRINGS = [
        UPLOAD_ERR_INI_SIZE   => 'uploader.error.size',
        UPLOAD_ERR_FORM_SIZE  => 'uploader.error.size',
        UPLOAD_ERR_PARTIAL    => 'uploader.error.partial',
        UPLOAD_ERR_NO_FILE    => 'uploader.error.no-file',
        UPLOAD_ERR_NO_TMP_DIR => 'uploader.error.no-temp',
        UPLOAD_ERR_CANT_WRITE => 'uploader.error.cannot-write',
        UPLOAD_ERR_EXTENSION  => 'uploader.error.php-extension'
    ];

    /**
     * Destination of uploaded file
     *
     * @var string
     */
    protected $destination;

    /**
     * Uploader options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Array containing uploaded files
     *
     * @var array
     */
    protected $uploadedFiles = [];

    /**
     * Create a new Uploader instance
     */
    public function __construct(string $destination, array $options = [])
    {
        $this->destination = FileSystem::normalize($destination);
        $this->options = array_merge($this->defaults(), $options);
    }

    /**
     * Return Uploader default options
     *
     * @return array
     */
    public function defaults()
    {
        $mimeTypes = array_map(
            [MimeType::class, 'fromExtension'],
            Formwork::instance()->option('files.allowed_extensions')
        );
        return [
            'allowedMimeTypes' => $mimeTypes,
            'overwrite'        => false,
        ];
    }

    /**
     * Upload one or more files
     *
     * @return bool Whether files were uploaded or not
     */
    public function upload(?string $name = null)
    {
        if (!HTTPRequest::hasFiles()) {
            return false;
        }
        $count = count(HTTPRequest::files());

        foreach (HTTPRequest::files() as $file) {
            if ($file['error'] === 0) {
                if ($name === null || $count > 1) {
                    $name = $file['name'];
                }
                $this->move($file['tmp_name'], $this->destination, $name);
            } else {
                throw new TranslatedException(self::ERROR_MESSAGES[$file['error']], self::ERROR_LANGUAGE_STRINGS[$file['error']]);
            }
        }

        return true;
    }

    /**
     * Return if a MIME type is allowed by Formwork
     *
     * @return bool
     */
    public function isAllowedMimeType(string $mimeType)
    {
        if ($this->options['allowedMimeTypes'] === null) {
            return true;
        }
        return in_array($mimeType, (array) $this->options['allowedMimeTypes'], true);
    }

    /**
     * Return uploaded files
     *
     * @return array
     */
    public function uploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * Move uploaded file to a destination
     *
     * @return bool Whether file was successfully moved or not
     */
    private function move(string $source, string $destination, string $filename)
    {
        $mimeType = FileSystem::mimeType($source);

        if (!$this->isAllowedMimeType($mimeType)) {
            throw new TranslatedException('MIME type ' . $mimeType . ' is not allowed', 'uploader.error.mime-type');
        }

        $destination = FileSystem::normalize($destination);

        if (basename($filename)[0] === '.') {
            throw new TranslatedException('Hidden file ' . $filename . ' not allowed', 'uploader.error.hidden-files');
        }

        $name = str_replace([' ', '.'], '-', FileSystem::name($filename));
        $extension = strtolower(FileSystem::extension($filename));

        if (empty($extension)) {
            $mimeToExt = MimeType::toExtension($mimeType);
            $extension = is_array($mimeToExt) ? $mimeToExt[0] : $mimeToExt;
        }

        $filename = $name . '.' . $extension;

        if (!(bool) preg_match('/^[a-z0-9_-]+(?:\.[a-z0-9]+)?$/i', $filename)) {
            throw new TranslatedException('Invalid file name ' . $filename, 'uploader.error.file-name');
        }

        if (!$this->options['overwrite'] && FileSystem::exists($destination . $filename)) {
            throw new TranslatedException('File ' . $filename . ' already exists', 'uploader.error.already-exists');
        }

        if (@move_uploaded_file($source, $destination . $filename)) {
            $this->uploadedFiles[] = $filename;
            return true;
        }

        return false;
    }
}
