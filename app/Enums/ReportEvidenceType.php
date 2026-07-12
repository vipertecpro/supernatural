<?php

namespace App\Enums;

enum ReportEvidenceType: string
{
    case Explanation = 'explanation';
    case MediaReference = 'media_reference';
    case SourceReference = 'source_reference';
    case CitationReference = 'citation_reference';
    case ExternalUrl = 'external_url';
    case MetadataSnapshot = 'metadata_snapshot';
    case Internal = 'internal';
}
