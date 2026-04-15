import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../../wayfinder'
/**
* @see \App\Filament\Development\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Development/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/development/dealerships'
*/
const ListDealerships = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListDealerships.url(options),
    method: 'get',
})

ListDealerships.definition = {
    methods: ["get","head"],
    url: '/development/dealerships',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Development\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Development/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/development/dealerships'
*/
ListDealerships.url = (options?: RouteQueryOptions) => {
    return ListDealerships.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Development\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Development/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/development/dealerships'
*/
ListDealerships.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListDealerships.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Development\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Development/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/development/dealerships'
*/
ListDealerships.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListDealerships.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Development\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Development/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/development/dealerships'
*/
const ListDealershipsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListDealerships.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Development\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Development/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/development/dealerships'
*/
ListDealershipsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListDealerships.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Development\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Development/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/development/dealerships'
*/
ListDealershipsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListDealerships.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListDealerships.form = ListDealershipsForm

export default ListDealerships