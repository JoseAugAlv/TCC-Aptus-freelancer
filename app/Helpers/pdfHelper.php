<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class PdfHelper
{
    public static function gerar($html, $nomeArquivo = 'relatorio.pdf', $orientacao = 'P', $tamanho = 'A4')
    {
        if (ob_get_length()) {
            ob_end_clean();
        }
        ob_start();
        
        $pdf = new \TCPDF($orientacao, 'mm', $tamanho, true, 'UTF-8', false);
        
        $pdf->SetCreator('Aptus');
        $pdf->SetAuthor('Aptus');
        $pdf->SetTitle($nomeArquivo);
        $pdf->SetSubject('Relatorio');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        
        ob_end_clean();
        
        $pdf->Output($nomeArquivo, 'D');
        exit;
    }
}