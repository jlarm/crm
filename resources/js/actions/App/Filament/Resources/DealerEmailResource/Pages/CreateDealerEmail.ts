import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\CreateDealerEmail::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/CreateDealerEmail.php:7
* @route '/admin/dealer-emails/create'
*/
const CreateDealerEmail = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateDealerEmail.url(options),
    method: 'get',
})

CreateDealerEmail.definition = {
    methods: ["get","head"],
    url: '/admin/dealer-emails/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\CreateDealerEmail::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/CreateDealerEmail.php:7
* @route '/admin/dealer-emails/create'
*/
CreateDealerEmail.url = (options?: RouteQueryOptions) => {
    return CreateDealerEmail.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\CreateDealerEmail::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/CreateDealerEmail.php:7
* @route '/admin/dealer-emails/create'
*/
CreateDealerEmail.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateDealerEmail.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\CreateDealerEmail::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/CreateDealerEmail.php:7
* @route '/admin/dealer-emails/create'
*/
CreateDealerEmail.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: CreateDealerEmail.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\CreateDealerEmail::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/CreateDealerEmail.php:7
* @route '/admin/dealer-emails/create'
*/
const CreateDealerEmailForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateDealerEmail.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\CreateDealerEmail::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/CreateDealerEmail.php:7
* @route '/admin/dealer-emails/create'
*/
CreateDealerEmailForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateDealerEmail.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\CreateDealerEmail::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/CreateDealerEmail.php:7
* @route '/admin/dealer-emails/create'
*/
CreateDealerEmailForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateDealerEmail.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

CreateDealerEmail.form = CreateDealerEmailForm

export default CreateDealerEmail