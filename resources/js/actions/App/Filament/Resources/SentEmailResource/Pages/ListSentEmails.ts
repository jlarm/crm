import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\SentEmailResource\Pages\ListSentEmails::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/ListSentEmails.php:7
* @route '/admin/sent-emails'
*/
const ListSentEmails = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListSentEmails.url(options),
    method: 'get',
})

ListSentEmails.definition = {
    methods: ["get","head"],
    url: '/admin/sent-emails',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\SentEmailResource\Pages\ListSentEmails::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/ListSentEmails.php:7
* @route '/admin/sent-emails'
*/
ListSentEmails.url = (options?: RouteQueryOptions) => {
    return ListSentEmails.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Resources\SentEmailResource\Pages\ListSentEmails::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/ListSentEmails.php:7
* @route '/admin/sent-emails'
*/
ListSentEmails.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListSentEmails.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\SentEmailResource\Pages\ListSentEmails::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/ListSentEmails.php:7
* @route '/admin/sent-emails'
*/
ListSentEmails.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListSentEmails.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\SentEmailResource\Pages\ListSentEmails::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/ListSentEmails.php:7
* @route '/admin/sent-emails'
*/
const ListSentEmailsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListSentEmails.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\SentEmailResource\Pages\ListSentEmails::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/ListSentEmails.php:7
* @route '/admin/sent-emails'
*/
ListSentEmailsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListSentEmails.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\SentEmailResource\Pages\ListSentEmails::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/ListSentEmails.php:7
* @route '/admin/sent-emails'
*/
ListSentEmailsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListSentEmails.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListSentEmails.form = ListSentEmailsForm

export default ListSentEmails