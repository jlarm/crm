import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\ViewDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/ViewDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/{record}'
*/
const ViewDealerEmailTemplate = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ViewDealerEmailTemplate.url(args, options),
    method: 'get',
})

ViewDealerEmailTemplate.definition = {
    methods: ["get","head"],
    url: '/admin/dealer-email-templates/{record}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\ViewDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/ViewDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/{record}'
*/
ViewDealerEmailTemplate.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return ViewDealerEmailTemplate.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\ViewDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/ViewDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/{record}'
*/
ViewDealerEmailTemplate.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ViewDealerEmailTemplate.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\ViewDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/ViewDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/{record}'
*/
ViewDealerEmailTemplate.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ViewDealerEmailTemplate.url(args, options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\ViewDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/ViewDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/{record}'
*/
const ViewDealerEmailTemplateForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ViewDealerEmailTemplate.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\ViewDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/ViewDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/{record}'
*/
ViewDealerEmailTemplateForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ViewDealerEmailTemplate.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\ViewDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/ViewDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/{record}'
*/
ViewDealerEmailTemplateForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ViewDealerEmailTemplate.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ViewDealerEmailTemplate.form = ViewDealerEmailTemplateForm

export default ViewDealerEmailTemplate