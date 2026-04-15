import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/admin/dealerships'
*/
const ListDealerships = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListDealerships.url(options),
    method: 'get',
})

ListDealerships.definition = {
    methods: ["get","head"],
    url: '/admin/dealerships',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/admin/dealerships'
*/
ListDealerships.url = (options?: RouteQueryOptions) => {
    return ListDealerships.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/admin/dealerships'
*/
ListDealerships.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListDealerships.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/admin/dealerships'
*/
ListDealerships.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListDealerships.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/admin/dealerships'
*/
const ListDealershipsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListDealerships.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/admin/dealerships'
*/
ListDealershipsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListDealerships.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/admin/dealerships'
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