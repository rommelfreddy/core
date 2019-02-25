<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Document;

use Shopware\Core\Checkout\Document\Exception\InvalidDocumentGeneratorTypeException;
use Shopware\Core\Checkout\Document\Exception\InvalidFileGeneratorTypeException;
use Shopware\Core\Checkout\Document\FileGenerator\FileTypes;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DocumentGeneratorController extends AbstractController
{
    /**
     * @var DocumentService
     */
    protected $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
    /**
     * @Route("/api/v{version}/_action/order/{orderId}/document/{documentTypeId}", name="api.action.document.invoice", methods={"POST"})
     *
     * @throws InvalidDocumentGeneratorTypeException
     * @throws InvalidFileGeneratorTypeException
     */
    public function createDocument(Request $request, string $orderId, string $documentTypeId, Context $context): JsonResponse
    {
        $fileType = $request->query->getAlnum('fileType', FileTypes::PDF);
        $config = DocumentConfigurationFactory::createConfiguration($request->get('config', []));

        $documentId = $this->documentService->create(
            $orderId,
            $documentTypeId,
            $fileType,
            $config,
            $context
        );

        return new JsonResponse(['documentId' => $documentId]);
    }
}
