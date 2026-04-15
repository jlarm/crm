import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipProgresses::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipProgresses.php:7
* @route '/admin/dealerships/{record}/progresses'
*/
const ManageDealershipProgresses = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ManageDealershipProgresses.url(args, options),
    method: 'get',
})

ManageDealershipProgresses.definition = {
    methods: ["get","head"],
    url: '/admin/dealerships/{record}/progresses',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipProgresses::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipProgresses.php:7
* @route '/admin/dealerships/{record}/progresses'
*/
ManageDealershipProgresses.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return ManageDealershipProgresses.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipProgresses::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipProgresses.php:7
* @route '/admin/dealerships/{record}/progresses'
*/
ManageDealershipProgresses.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ManageDealershipProgresses.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipProgresses::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipProgresses.php:7
* @route '/admin/dealerships/{record}/progresses'
*/
ManageDealershipProgresses.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ManageDealershipProgresses.url(args, options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipProgresses::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipProgresses.php:7
* @route '/admin/dealerships/{record}/progresses'
*/
const ManageDealershipProgressesForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ManageDealershipProgresses.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipProgresses::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipProgresses.php:7
* @route '/admin/dealerships/{record}/progresses'
*/
ManageDealershipProgressesForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ManageDealershipProgresses.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipProgresses::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipProgresses.php:7
* @route '/admin/dealerships/{record}/progresses'
*/
ManageDealershipProgressesForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ManageDealershipProgresses.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ManageDealershipProgresses.form = ManageDealershipProgressesForm

export default ManageDealershipProgresses