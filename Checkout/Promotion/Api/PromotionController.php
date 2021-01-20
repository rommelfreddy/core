<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Promotion\Api;

use Shopware\Core\Checkout\Promotion\Util\PromotionCodeService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Routing\Annotation\Acl;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class PromotionController extends AbstractController
{
    /**
     * @var PromotionCodeService
     */
    private $codeService;

    public function __construct(PromotionCodeService $codeService)
    {
        $this->codeService = $codeService;
    }

    /**
     * @Since("6.4.0.0")
     * @Route("/api/v{version}/_action/promotion/codes/generate-fixed", name="api.action.promotion.codes.generate-fixed", methods={"GET"})
     * @Acl({"promotion.editor"})
     *
     * @throws NotFoundHttpException
     */
    public function generateFixedCode(): Response
    {
        if (!Feature::isActive('FEATURE_NEXT_12016')) {
            throw new NotFoundHttpException('Route not found, due to inactive flag FEATURE_NEXT_12016');
        }

        return new JsonResponse($this->codeService->getFixedCode());
    }

    /**
     * @Since("6.4.0.0")
     * @Route("/api/v{version}/_action/promotion/codes/generate-individual", name="api.action.promotion.codes.generate-individual", methods={"GET"})
     * @Acl({"promotion.editor"})
     *
     * @throws NotFoundHttpException
     */
    public function generateIndividualCodes(Request $request): Response
    {
        if (!Feature::isActive('FEATURE_NEXT_12016')) {
            throw new NotFoundHttpException('Route not found, due to inactive flag FEATURE_NEXT_12016');
        }

        $codePattern = $request->query->get('codePattern');
        $amount = (int) $request->query->get('amount');

        return new JsonResponse($this->codeService->generateIndividualCodes($codePattern, $amount));
    }

    /**
     * @Since("6.4.0.0")
     * @Route("/api/v{version}/_action/promotion/codes/replace-individual", name="api.action.promotion.codes.replace-individual", methods={"PATCH"})
     * @Acl({"promotion.editor"})
     *
     * @throws NotFoundHttpException
     */
    public function replaceIndividualCodes(Request $request, Context $context): Response
    {
        if (!Feature::isActive('FEATURE_NEXT_12016')) {
            throw new NotFoundHttpException('Route not found, due to inactive flag FEATURE_NEXT_12016');
        }

        $promotionId = $request->request->get('promotionId');
        $codePattern = $request->request->get('codePattern');
        $amount = $request->request->get('amount');

        $this->codeService->replaceIndividualCodes($promotionId, $context, $codePattern, $amount);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Since("6.4.0.0")
     * @Route("/api/v{version}/_action/promotion/codes/preview", name="api.action.promotion.codes.preview", methods={"GET"})
     * @Acl({"promotion.editor"})
     *
     * @throws NotFoundHttpException
     */
    public function getCodePreview(Request $request): Response
    {
        if (!Feature::isActive('FEATURE_NEXT_12016')) {
            throw new NotFoundHttpException('Route not found, due to inactive flag FEATURE_NEXT_12016');
        }

        return new JsonResponse($this->codeService->getPreview($request->query->get('codePattern')));
    }
}
