import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\ListDealerEmailTemplates::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/ListDealerEmailTemplates.php:7
* @route '/admin/dealer-email-templates'
*/
const ListDealerEmailTemplates = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListDealerEmailTemplates.url(options),
    method: 'get',
})

ListDealerEmailTemplates.definition = {
    methods: ["get","head"],
    url: '/admin/dealer-email-templates',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\ListDealerEmailTemplates::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/ListDealerEmailTemplates.php:7
* @route '/admin/dealer-email-templates'
*/
ListDealerEmailTemplates.url = (options?: RouteQueryOptions) => {
    return ListDealerEmailTemplates.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\ListDealerEmailTemplates::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/ListDealerEmailTemplates.php:7
* @route '/admin/dealer-email-templates'
*/
ListDealerEmailTemplates.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListDealerEmailTemplates.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\ListDealerEmailTemplates::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/ListDealerEmailTemplates.php:7
* @route '/admin/dealer-email-templates'
*/
ListDealerEmailTemplates.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListDealerEmailTemplates.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\ListDealerEmailTemplates::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/ListDealerEmailTemplates.php:7
* @route '/admin/dealer-email-templates'
*/
const ListDealerEmailTemplatesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListDealerEmailTemplates.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\ListDealerEmailTemplates::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/ListDealerEmailTemplates.php:7
* @route '/admin/dealer-email-templates'
*/
ListDealerEmailTemplatesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListDealerEmailTemplates.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\ListDealerEmailTemplates::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/ListDealerEmailTemplates.php:7
* @route '/admin/dealer-email-templates'
*/
ListDealerEmailTemplatesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListDealerEmailTemplates.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListDealerEmailTemplates.form = ListDealerEmailTemplatesForm

export default ListDealerEmailTemplates