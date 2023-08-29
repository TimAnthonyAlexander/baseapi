<?php

namespace timanthonyalexander\BaseApi\model\File;

use Exception;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use timanthonyalexander\BaseApi\model\Entity\EntityModel;

class FileModel extends EntityModel
{
    public string $name;
    public string $type;
    public string $created; // Y-m-d H:i:s
    public string $content;
    public string $updated;

    /**
     * @throws Exception
     * @param  array<string,mixed> $file
     */
    public static function uploadFile(array $file, bool $autoSave = false): static
    {
        $allowedTypes = [
            'image/png',
            'image/jpeg',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.ms-excel',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.oasis.opendocument.presentation',
            'application/vnd.oasis.opendocument.spreadsheet',
            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.graphics',
            'application/vnd.oasis.opendocument.chart',
            'application/vnd.oasis.opendocument.formula',
            'application/vnd.oasis.opendocument.image',
            'application/vnd.oasis.opendocument.text-master',
            'application/vnd.oasis.opendocument.text-web',
            'application/vnd.oasis.opendocument.database',
            'application/vnd.sun.xml.calc',
            'application/vnd.sun.xml.draw',
            'application/vnd.sun.xml.impress',
            'application/vnd.sun.xml.writer',
            'application/vnd.sun.xml.math',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'application/vnd.openxmlformats-officedocument.presentationml.template',
            'application/x-iwork-keynote-sffkey',
            'application/x-iwork-pages-sffpages',
            'application/x-iwork-numbers-sffnumbers',
            'application/x-iwork-numbers-sfftemplate',
            'application/x-iwork-pages-sfftemplate',
            'application/x-iwork-keynote-sffkth',
            'application/x-iwork-keynote-sfftemplate',
            'application/x-iwork-pages-sffkey',
            'application/x-iwork-numbers-sffkth',
            'image/heic',
            'image/heif',
            'text/plain',
            'text/rtf',
            // All kinds of video and audio files
            'audio/mpeg',
            'audio/x-mpeg',
            'video/mpeg',
            'video/x-mpeg',
            'audio/mpeg3',
            'audio/x-mpeg-3',
            'audio/mp3',
            'video/mp4',
            'video/x-mp4',
            'audio/m4a',
            'audio/x-m4a',
            'video/quicktime',
            'application/x-troff-msvideo',
            'video/avi',
            'video/msvideo',
            'video/x-msvideo',
            'video/x-ms-asf',
            'video/x-ms-asx',
            'video/x-ms-wm',
            'video/x-ms-wmv',
            'video/x-ms-wmx',
            'video/x-ms-wvx',
            'video/x-ms-wmz',
            'video/x-ms-wvx',
            'audio/wav',
            'audio/x-wav',
            'audio/x-pn-wav',
            'audio/ogg',
            'audio/x-ogg',
            'video/ogg',
            'video/x-ogg',
        ];

        if (!in_array($file['type'] ?? 'NOTALLOWED', $allowedTypes, true)) {
            throw new Exception('File (type) not allowed: ' . json_encode($file));
        }

        $staticFileModel = new static(md5(base64_encode((string) microtime(true))));
        $moveTo = __DIR__ . '/../../../dump/' . $staticFileModel->id;

        move_uploaded_file($file['tmp_name'], $moveTo);

        $staticFileModel->name = $file['name'];
        $staticFileModel->type = $file['type'];
        $staticFileModel->created = date('Y-m-d H:i:s');
        $staticFileModel->content = __DIR__ . '/../../../dump/' . $staticFileModel->id;

        if (in_array($file['type'], ['image/png', 'image/jpeg'], true)) {
            $optimizerChain = OptimizerChainFactory::create();
            $optimizerChain->optimize($staticFileModel->content);
        }

        if ($autoSave) {
            $staticFileModel->save();
        }

        return $staticFileModel;
    }
}
