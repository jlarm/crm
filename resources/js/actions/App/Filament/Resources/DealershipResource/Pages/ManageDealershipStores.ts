import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipStores::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipStores.php:7
* @route '/admin/dealerships/{record}/stores'
*/
const ManageDealershipStores = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ManageDealershipStores.url(args, options),
    method: 'get',
})

ManageDealershipStores.definition = {
    methods: ["get","head"],
    url: '/admin/dealerships/{record}/stores',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipStores::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipStores.php:7
* @route '/admin/dealerships/{record}/stores'
*/
ManageDealershipStores.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return ManageDealershipStores.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipStores::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipStores.php:7
* @route '/admin/dealerships/{record}/stores'
*/
ManageDealershipStores.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ManageDealershipStores.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipStores::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipStores.php:7
* @route '/admin/dealerships/{record}/stores'
*/
ManageDealershipStores.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ManageDealershipStores.url(args, options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipStores::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipStores.php:7
* @route '/admin/dealerships/{record}/stores'
*/
const ManageDealershipStoresForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ManageDealershipStores.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipStores::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipStores.php:7
* @route '/admin/dealerships/{record}/stores'
*/
ManageDealershipStoresForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ManageDealershipStores.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipStores::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipStores.php:7
* @route '/admin/dealerships/{record}/stores'
*/
ManageDealershipStoresForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ManageDealershipStores.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ManageDealershipStores.form = ManageDealershipStoresForm

export default ManageDealershipStores