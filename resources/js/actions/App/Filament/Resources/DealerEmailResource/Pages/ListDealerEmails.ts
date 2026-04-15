import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\ListDealerEmails::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/ListDealerEmails.php:7
* @route '/admin/dealer-emails'
*/
const ListDealerEmails = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListDealerEmails.url(options),
    method: 'get',
})

ListDealerEmails.definition = {
    methods: ["get","head"],
    url: '/admin/dealer-emails',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\ListDealerEmails::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/ListDealerEmails.php:7
* @route '/admin/dealer-emails'
*/
ListDealerEmails.url = (options?: RouteQueryOptions) => {
    return ListDealerEmails.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\ListDealerEmails::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/ListDealerEmails.php:7
* @route '/admin/dealer-emails'
*/
ListDealerEmails.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListDealerEmails.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\ListDealerEmails::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/ListDealerEmails.php:7
* @route '/admin/dealer-emails'
*/
ListDealerEmails.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListDealerEmails.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\ListDealerEmails::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/ListDealerEmails.php:7
* @route '/admin/dealer-emails'
*/
const ListDealerEmailsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListDealerEmails.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\ListDealerEmails::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/ListDealerEmails.php:7
* @route '/admin/dealer-emails'
*/
ListDealerEmailsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListDealerEmails.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealerEmailResource\Pages\ListDealerEmails::__invoke
* @see app/Filament/Resources/DealerEmailResource/Pages/ListDealerEmails.php:7
* @route '/admin/dealer-emails'
*/
ListDealerEmailsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListDealerEmails.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListDealerEmails.form = ListDealerEmailsForm

export default ListDealerEmails