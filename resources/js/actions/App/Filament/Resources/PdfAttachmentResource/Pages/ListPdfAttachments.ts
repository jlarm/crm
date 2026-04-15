import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\ListPdfAttachments::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/ListPdfAttachments.php:7
* @route '/admin/pdf-attachments'
*/
const ListPdfAttachments = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListPdfAttachments.url(options),
    method: 'get',
})

ListPdfAttachments.definition = {
    methods: ["get","head"],
    url: '/admin/pdf-attachments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\ListPdfAttachments::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/ListPdfAttachments.php:7
* @route '/admin/pdf-attachments'
*/
ListPdfAttachments.url = (options?: RouteQueryOptions) => {
    return ListPdfAttachments.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\ListPdfAttachments::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/ListPdfAttachments.php:7
* @route '/admin/pdf-attachments'
*/
ListPdfAttachments.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListPdfAttachments.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\ListPdfAttachments::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/ListPdfAttachments.php:7
* @route '/admin/pdf-attachments'
*/
ListPdfAttachments.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListPdfAttachments.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\ListPdfAttachments::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/ListPdfAttachments.php:7
* @route '/admin/pdf-attachments'
*/
const ListPdfAttachmentsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListPdfAttachments.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\ListPdfAttachments::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/ListPdfAttachments.php:7
* @route '/admin/pdf-attachments'
*/
ListPdfAttachmentsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListPdfAttachments.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\ListPdfAttachments::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/ListPdfAttachments.php:7
* @route '/admin/pdf-attachments'
*/
ListPdfAttachmentsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListPdfAttachments.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListPdfAttachments.form = ListPdfAttachmentsForm

export default ListPdfAttachments