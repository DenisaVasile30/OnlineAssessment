<?php

namespace App\Helper\FormatHelper;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\File;

class StreamToFileTransformer implements DataTransformerInterface
{
    /**
     * Transforms a resource (stream) into a File object.
     *
     * @param resource|null $stream
     *
     * @return File|null
     */
    public function transform($stream)
    {
        if (!$stream) {
            return null;
        }

        // Create a temporary file to hold the stream contents
        $tmpfile = tmpfile();
        stream_copy_to_stream($stream, $tmpfile);

        // Create a new File object with the contents of the temporary file
        return new File(stream_get_meta_data($tmpfile)['uri']);
    }

    /**
     * Transforms a File object into a resource (stream).
     *
     * @param File|null $file
     *
     * @return resource|null
     */
    public function reverseTransform($file)
    {
        if (!$file) {
            return null;
        }

        // Open the file and return a resource (stream)
        return fopen($file->getRealPath(), 'r');
    }
}