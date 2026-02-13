<?php

namespace App\Service\Pdf;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Service\Pdf\Interfaces\ExportableDocumentInterface;
use Twig\Environment;

class PdfGeneratorService
{
    public function __construct(private Environment $twig) {}

    public function generate(ExportableDocumentInterface $document): string
    {
        $html = $this->twig->render($document->getTemplate(), $document->getData());

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
