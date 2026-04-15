import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\CreateDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/CreateDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/create'
*/
const CreateDealerEmailTemplate = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateDealerEmailTemplate.url(options),
    method: 'get',
})

CreateDealerEmailTemplate.definition = {
    methods: ["get","head"],
    url: '/admin/dealer-email-templates/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\CreateDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/CreateDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/create'
*/
CreateDealerEmailTemplate.url = (options?: RouteQueryOptions) => {
    return CreateDealerEmailTemplate.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\CreateDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/CreateDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/create'
*/
CreateDealerEmailTemplate.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateDealerEmailTemplate.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\CreateDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/CreateDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/create'
*/
CreateDealerEmailTemplate.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: CreateDealerEmailTemplate.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\CreateDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/CreateDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/create'
*/
const CreateDealerEmailTemplateForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateDealerEmailTemplate.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\CreateDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/CreateDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/create'
*/
CreateDealerEmailTemplateForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateDealerEmailTemplate.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailTemplateResource\Pages\CreateDealerEmailTemplate::__invoke
* @see app/Filament/Resources/DealerEmailTemplateResource/Pages/CreateDealerEmailTemplate.php:7
* @route '/admin/dealer-email-templates/create'
*/
CreateDealerEmailTemplateForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateDealerEmailTemplate.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

CreateDealerEmailTemplate.form = CreateDealerEmailTemplateForm

export default CreateDealerEmailTemplate