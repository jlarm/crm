import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\EditDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/EditDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/{record}/edit'
*/
const EditDealerEmailTemplate = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditDealerEmailTemplate.url(args, options),
    method: 'get',
})

EditDealerEmailTemplate.definition = {
    methods: ["get","head"],
    url: '/admin/dealer-email-templates/{record}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\EditDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/EditDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/{record}/edit'
*/
EditDealerEmailTemplate.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return EditDealerEmailTemplate.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\EditDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/EditDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/{record}/edit'
*/
EditDealerEmailTemplate.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditDealerEmailTemplate.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\EditDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/EditDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/{record}/edit'
*/
EditDealerEmailTemplate.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: EditDealerEmailTemplate.url(args, options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\EditDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/EditDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/{record}/edit'
*/
const EditDealerEmailTemplateForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditDealerEmailTemplate.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\EditDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/EditDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/{record}/edit'
*/
EditDealerEmailTemplateForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditDealerEmailTemplate.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\EditDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/EditDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/{record}/edit'
*/
EditDealerEmailTemplateForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditDealerEmailTemplate.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

EditDealerEmailTemplate.form = EditDealerEmailTemplateForm

export default EditDealerEmailTemplate