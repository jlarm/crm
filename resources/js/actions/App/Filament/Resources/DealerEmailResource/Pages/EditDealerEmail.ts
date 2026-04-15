import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\EditDealerEmail::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/EditDealerEmail.php:7
* @route '/admin/dealer-emails/{record}/edit'
*/
const EditDealerEmail = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditDealerEmail.url(args, options),
    method: 'get',
})

EditDealerEmail.definition = {
    methods: ["get","head"],
    url: '/admin/dealer-emails/{record}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\EditDealerEmail::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/EditDealerEmail.php:7
* @route '/admin/dealer-emails/{record}/edit'
*/
EditDealerEmail.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return EditDealerEmail.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\EditDealerEmail::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/EditDealerEmail.php:7
* @route '/admin/dealer-emails/{record}/edit'
*/
EditDealerEmail.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditDealerEmail.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\EditDealerEmail::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/EditDealerEmail.php:7
* @route '/admin/dealer-emails/{record}/edit'
*/
EditDealerEmail.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: EditDealerEmail.url(args, options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\EditDealerEmail::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/EditDealerEmail.php:7
* @route '/admin/dealer-emails/{record}/edit'
*/
const EditDealerEmailForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditDealerEmail.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\EditDealerEmail::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/EditDealerEmail.php:7
* @route '/admin/dealer-emails/{record}/edit'
*/
EditDealerEmailForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditDealerEmail.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\EditDealerEmail::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/EditDealerEmail.php:7
* @route '/admin/dealer-emails/{record}/edit'
*/
EditDealerEmailForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditDealerEmail.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

EditDealerEmail.form = EditDealerEmailForm

export default EditDealerEmail