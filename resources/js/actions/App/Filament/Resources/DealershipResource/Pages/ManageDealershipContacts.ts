import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipContacts::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipContacts.php:7
* @route '/admin/dealerships/{record}/contacts'
*/
const ManageDealershipContacts = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ManageDealershipContacts.url(args, options),
    method: 'get',
})

ManageDealershipContacts.definition = {
    methods: ["get","head"],
    url: '/admin/dealerships/{record}/contacts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipContacts::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipContacts.php:7
* @route '/admin/dealerships/{record}/contacts'
*/
ManageDealershipContacts.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return ManageDealershipContacts.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipContacts::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipContacts.php:7
* @route '/admin/dealerships/{record}/contacts'
*/
ManageDealershipContacts.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ManageDealershipContacts.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipContacts::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipContacts.php:7
* @route '/admin/dealerships/{record}/contacts'
*/
ManageDealershipContacts.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ManageDealershipContacts.url(args, options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipContacts::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipContacts.php:7
* @route '/admin/dealerships/{record}/contacts'
*/
const ManageDealershipContactsForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ManageDealershipContacts.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipContacts::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipContacts.php:7
* @route '/admin/dealerships/{record}/contacts'
*/
ManageDealershipContactsForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ManageDealershipContacts.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipContacts::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipContacts.php:7
* @route '/admin/dealerships/{record}/contacts'
*/
ManageDealershipContactsForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ManageDealershipContacts.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ManageDealershipContacts.form = ManageDealershipContactsForm

export default ManageDealershipContacts