<?php

namespace LLPhant\Chat\Vision;

use JsonSerializable;

class ImageSource implements JsonSerializable
{
    private readonly string $url;

    public function __construct(string $urlOrBase64Image, private readonly ImageQuality $detail = ImageQuality::Auto)
    {
        if ($this->isUrl($urlOrBase64Image)) {
            $this->url = $urlOrBase64Image;
        } elseif ($this->isBase64($urlOrBase64Image)) {
            $this->url = 'data:image/jpeg;base64,'.$urlOrBase64Image;
        } else {
            throw new \InvalidArgumentException('Invalid image URL or base64 format.');
        }
    }

    protected function isUrl(string $image): bool
    {
        return \filter_var($image, FILTER_VALIDATE_URL) !== false;
    }

    protected function isBase64(string $image): bool
    {
        return \preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $image) === 1;
    }

    /**
     * @return array{type: string, image_url: array{url: string, details: string}}
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'image_url',
            'image_url' => [
                'url' => $this->url,
                'details' => $this->detail->value,
            ],
        ];
    }
}
