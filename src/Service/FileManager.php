<?php

namespace App\Service;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

class FileManager
{
    public function __construct(
        private S3Client $s3Client,
        private string   $bucketName
    )
    {
    }

    /**
     * @param string $key
     * @param string $filePath
     * @throws \Exception
     */
    public function uploadFile(string $key, string $filePath): void
    {
        try {
            $object = [
                'Bucket' => $this->bucketName,
                'Key' => $key,
                'Body' => fopen($filePath, 'r'),
                'ACL' => 'public-read'
            ];

            $this->s3Client->putObject($object);
        } catch (S3Exception $e) {
            throw new \Exception("An error occurred during uploading to s3:  {$e->getMessage()}");
        }
    }

    /**
     * @param string $key
     * @return string
     * @throws \Exception
     */
    public function getFile(string $key): string
    {
        try {
            $object = $this->s3Client->getObject([
                'Bucket' => $this->bucketName,
                'Key' => $key
            ]);

            return $object['Body'];
        } catch (S3Exception $e) {
            throw new \Exception("An error occurred during downloading a file: {$e->getMessage()}");
        }
    }

    /**
     * @param string $key
     * @throws \Exception
     */
    public function deleteFile(string $key): void
    {
        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucketName,
                'Key' => $key
            ]);
        } catch (S3Exception $e) {
            throw new \Exception("An error occurred during delete a file: {$e->getMessage()}");
        }
    }

    public function checkFileExist(string $key): bool
    {
        return $this->s3Client->doesObjectExist($this->bucketName, $key);
    }
}
