<?php

namespace App\Controller;

use App\Entity\Favourite;
use App\Entity\Product;
use App\Repository\FavouriteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FavouriteController extends AbstractController
{
    public function __construct(
        private readonly FavouriteRepository $favouriteRepository
    )
    {
    }

    #[Route('/favourite', name: 'app_favourite')]
    public function index(Request $request): Response
    {
        return $this->render('favourite/index.html.twig', [
            'favourites' => $this->getUser()->getFavourites(),
            'numberOfProduct' => array_sum($request->getSession()->get("shopping_basket", []))
        ]);
    }

    private function redirectToPreviousPage(Request $request): Response
    {
        $previousUrl = $request->headers->get("Referer");

        if (
            $previousUrl &&
            $previousUrl !== $this->generateUrl('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL) &&
            $previousUrl !== $this->generateUrl('app_register', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ) {
            return new RedirectResponse($previousUrl);
        }

        return new RedirectResponse(
            $this->generateUrl("app_home")
        );
    }

    #[Route('/favourite/{id}/add', name: 'app_add_favourite')]
    public function addProductFavourite(Product $product, Request $request): Response
    {
        /** @var Favourite[] $favourites */
        $favourites = $this->getUser()->getFavourites();

        foreach ($favourites as $favourite) {
            if ($favourite->getProduct()->getId() === $product->getId()) {
                $this->addFlash("success", "the product already exist in favourites");
                return $this->redirectTopreviousPage($request);
            }
        }

        $favourite = new Favourite();
        $favourite->setUser($this->getUser());
        $favourite->setProduct($product);
        $this->favouriteRepository->save($favourite, true);

        $this->addFlash("success", "the product has been successfully added to favourites");
        return $this->redirectTopreviousPage($request);
    }

    #[Route('/favourite/{id}/remove', name: 'app_remove_favourite')]
    public function removeProductFavourite(Product $product, Request $request): Response
    {
        /** @var Favourite[] $favourites */
        $favourites = $this->getUser()->getFavourites();

        foreach ($favourites as $favourite) {
            if ($favourite->getProduct()->getId() === $product->getId()) {
                $this->favouriteRepository->remove($favourite, true);
                break;
            }
        }

        $this->addFlash("success", "the product has been successfully remove to favourites");
        return $this->redirectTopreviousPage($request);
    }
}
