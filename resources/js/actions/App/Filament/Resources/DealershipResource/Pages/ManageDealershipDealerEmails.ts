import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipDealerEmails::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipDealerEmails.php:7
* @route '/admin/dealerships/{record}/emails'
*/
const ManageDealershipDealerEmails = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ManageDealershipDealerEmails.url(args, options),
    method: 'get',
})

ManageDealershipDealerEmails.definition = {
    methods: ["get","head"],
    url: '/admin/dealerships/{record}/emails',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipDealerEmails::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipDealerEmails.php:7
* @route '/admin/dealerships/{record}/emails'
*/
ManageDealershipDealerEmails.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return ManageDealershipDealerEmails.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipDealerEmails::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipDealerEmails.php:7
* @route '/admin/dealerships/{record}/emails'
*/
ManageDealershipDealerEmails.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ManageDealershipDealerEmails.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipDealerEmails::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipDealerEmails.php:7
* @route '/admin/dealerships/{record}/emails'
*/
ManageDealershipDealerEmails.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ManageDealershipDealerEmails.url(args, options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipDealerEmails::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipDealerEmails.php:7
* @route '/admin/dealerships/{record}/emails'
*/
const ManageDealershipDealerEmailsForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ManageDealershipDealerEmails.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipDealerEmails::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipDealerEmails.php:7
* @route '/admin/dealerships/{record}/emails'
*/
ManageDealershipDealerEmailsForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ManageDealershipDealerEmails.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipDealerEmails::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipDealerEmails.php:7
* @route '/admin/dealerships/{record}/emails'
*/
ManageDealershipDealerEmailsForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ManageDealershipDealerEmails.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ManageDealershipDealerEmails.form = ManageDealershipDealerEmailsForm

export default ManageDealershipDealerEmails