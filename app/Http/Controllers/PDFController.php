<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Document;

class PDFController extends Controller
{
    public function generate($id)
    {
        $document = Document::findOrFail($id);

        $pdf = Pdf::loadView('pdf.document', compact('document'));

        return $pdf->download('document_'.$id.'.pdf');
    }
}
