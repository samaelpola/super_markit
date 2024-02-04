<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class GetPicture extends AbstractController
{
    public function __construct(
        private FileManager        $fileManager,
        private ProductRepository  $productRepository,
        private CategoryRepository $categoryRepository
    )
    {
    }

    #[Route('/picture/{id}', name: 'app_get_picture')]
    public function getPicture(int $id, #[MapQueryParameter] string $type): Response
    {
        if (!in_array(strtolower($type), ["category", "product"])) {
            throw new BadRequestHttpException(
                "Invalid type parameter. It should be category or product"
            );
        }

        /** @var Category|Product $entity */
        $entity = null;

        if (strtolower($type) === "category") {
            $entity = $this->categoryRepository->find($id);

            if ($entity === null) {
                throw new NotFoundHttpException("category {$id} not found");
            }
        }

        if (strtolower($type) === "product") {
            $entity = $this->productRepository->find($id);

            if ($entity === null) {
                throw new NotFoundHttpException("product {$id} not found");
            }
        }

        $file = $this->fileManager->getFile($entity->getPicture());

        if (strlen($file) === 0) {
            throw new NotFoundHttpException("picture of {$id} not found");
        }

        $extension = pathinfo($entity->getPicture(), PATHINFO_EXTENSION);
        $response = new StreamedResponse();
        $response->setCallback(function () use ($file) {
            echo $file;
        });

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "photo.{$extension}"
        );

        $response->headers->set("Content-Type", "image/{$extension}");
        $response->headers->set("Content-Disposition", $disposition);
        $response->headers->set("Content-Length", strlen($file));
        $response->headers->set("Expires", gmdate("D, d M Y H:i:s", strtotime("+1 week")) . " GMT");

        return $response;
    }
}
