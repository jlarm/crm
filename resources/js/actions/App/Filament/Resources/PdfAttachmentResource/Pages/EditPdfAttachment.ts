import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\EditPdfAttachment::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/EditPdfAttachment.php:7
* @route '/admin/pdf-attachments/{record}/edit'
*/
const EditPdfAttachment = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditPdfAttachment.url(args, options),
    method: 'get',
})

EditPdfAttachment.definition = {
    methods: ["get","head"],
    url: '/admin/pdf-attachments/{record}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\EditPdfAttachment::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/EditPdfAttachment.php:7
* @route '/admin/pdf-attachments/{record}/edit'
*/
EditPdfAttachment.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { record: args }
    }

    if (Array.isArray(args)) {
        args = {
            record: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        record: args.record,
    }

    return EditPdfAttachment.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\EditPdfAttachment::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/EditPdfAttachment.php:7
* @route '/admin/pdf-attachments/{record}/edit'
*/
EditPdfAttachment.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditPdfAttachment.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\EditPdfAttachment::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/EditPdfAttachment.php:7
* @route '/admin/pdf-attachments/{record}/edit'
*/
EditPdfAttachment.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: EditPdfAttachment.url(args, options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\EditPdfAttachment::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/EditPdfAttachment.php:7
* @route '/admin/pdf-attachments/{record}/edit'
*/
const EditPdfAttachmentForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditPdfAttachment.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\EditPdfAttachment::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/EditPdfAttachment.php:7
* @route '/admin/pdf-attachments/{record}/edit'
*/
EditPdfAttachmentForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditPdfAttachment.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\PdfAttachmentResource\Pages\EditPdfAttachment::__invoke
* @see app/Filament/Resources/PdfAttachmentResource/Pages/EditPdfAttachment.php:7
* @route '/admin/pdf-attachments/{record}/edit'
*/
EditPdfAttachmentForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditPdfAttachment.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

EditPdfAttachment.form = EditPdfAttachmentForm

export default EditPdfAttachment