import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\CreatePdfAttachment::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/CreatePdfAttachment.php:7
* @route '/admin/pdf-attachments/create'
*/
const CreatePdfAttachment = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreatePdfAttachment.url(options),
    method: 'get',
})

CreatePdfAttachment.definition = {
    methods: ["get","head"],
    url: '/admin/pdf-attachments/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\CreatePdfAttachment::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/CreatePdfAttachment.php:7
* @route '/admin/pdf-attachments/create'
*/
CreatePdfAttachment.url = (options?: RouteQueryOptions) => {
    return CreatePdfAttachment.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\CreatePdfAttachment::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/CreatePdfAttachment.php:7
* @route '/admin/pdf-attachments/create'
*/
CreatePdfAttachment.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreatePdfAttachment.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\CreatePdfAttachment::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/CreatePdfAttachment.php:7
* @route '/admin/pdf-attachments/create'
*/
CreatePdfAttachment.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: CreatePdfAttachment.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\CreatePdfAttachment::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/CreatePdfAttachment.php:7
* @route '/admin/pdf-attachments/create'
*/
const CreatePdfAttachmentForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreatePdfAttachment.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\CreatePdfAttachment::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/CreatePdfAttachment.php:7
* @route '/admin/pdf-attachments/create'
*/
CreatePdfAttachmentForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreatePdfAttachment.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\CreatePdfAttachment::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/CreatePdfAttachment.php:7
* @route '/admin/pdf-attachments/create'
*/
CreatePdfAttachmentForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreatePdfAttachment.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

CreatePdfAttachment.form = CreatePdfAttachmentForm

export default CreatePdfAttachment