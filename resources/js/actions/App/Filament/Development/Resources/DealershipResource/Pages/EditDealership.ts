import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../wayfinder'
/**
* @see \App\Filament\Development\Resources\DealershipResource\Pages\EditDealership::__invoke
* @see app/Filament/Development/Resources/DealershipResource/Pages/EditDealership.php:7
* @route '/development/dealerships/{record}/edit'
*/
const EditDealership = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditDealership.url(args, options),
    method: 'get',
})

EditDealership.definition = {
    methods: ["get","head"],
    url: '/development/dealerships/{record}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Development\Resources\DealershipResource\Pages\EditDealership::__invoke
* @see app/Filament/Development/Resources/DealershipResource/Pages/EditDealership.php:7
* @route '/development/dealerships/{record}/edit'
*/
EditDealership.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return EditDealership.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Filament\Development\Resources\DealershipResource\Pages\EditDealership::__invoke
* @see app/Filament/Development/Resources/DealershipResource/Pages/EditDealership.php:7
* @route '/development/dealerships/{record}/edit'
*/
EditDealership.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditDealership.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Development\Resources\DealershipResource\Pages\EditDealership::__invoke
* @see app/Filament/Development/Resources/DealershipResource/Pages/EditDealership.php:7
* @route '/development/dealerships/{record}/edit'
*/
EditDealership.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: EditDealership.url(args, options),
    method: 'head',
})

/**
* @see \App\Filament\Development\Resources\DealershipResource\Pages\EditDealership::__invoke
* @see app/Filament/Development/Resources/DealershipResource/Pages/EditDealership.php:7
* @route '/development/dealerships/{record}/edit'
*/
const EditDealershipForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditDealership.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Development\Resources\DealershipResource\Pages\EditDealership::__invoke
* @see app/Filament/Development/Resources/DealershipResource/Pages/EditDealership.php:7
* @route '/development/dealerships/{record}/edit'
*/
EditDealershipForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditDealership.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Development\Resources\DealershipResource\Pages\EditDealership::__invoke
* @see app/Filament/Development/Resources/DealershipResource/Pages/EditDealership.php:7
* @route '/development/dealerships/{record}/edit'
*/
EditDealershipForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditDealership.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

EditDealership.form = EditDealershipForm

export default EditDealership