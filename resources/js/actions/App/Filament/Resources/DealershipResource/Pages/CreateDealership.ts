import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\DealershipResource\Pages\CreateDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/CreateDealership.php:7
* @route '/admin/dealerships/create'
*/
const CreateDealership = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateDealership.url(options),
    method: 'get',
})

CreateDealership.definition = {
    methods: ["get","head"],
    url: '/admin/dealerships/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealershipResource\Pages\CreateDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/CreateDealership.php:7
* @route '/admin/dealerships/create'
*/
CreateDealership.url = (options?: RouteQueryOptions) => {
    return CreateDealership.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealershipResource\Pages\CreateDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/CreateDealership.php:7
* @route '/admin/dealerships/create'
*/
CreateDealership.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateDealership.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\CreateDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/CreateDealership.php:7
* @route '/admin/dealerships/create'
*/
CreateDealership.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: CreateDealership.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\CreateDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/CreateDealership.php:7
* @route '/admin/dealerships/create'
*/
const CreateDealershipForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateDealership.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\CreateDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/CreateDealership.php:7
* @route '/admin/dealerships/create'
*/
CreateDealershipForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateDealership.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\CreateDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/CreateDealership.php:7
* @route '/admin/dealerships/create'
*/
CreateDealershipForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateDealership.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

CreateDealership.form = CreateDealershipForm

export default CreateDealership