import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\SentEmailResource\Pages\CreateSentEmail::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/CreateSentEmail.php:7
* @route '/admin/sent-emails/create'
*/
const CreateSentEmail = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateSentEmail.url(options),
    method: 'get',
})

CreateSentEmail.definition = {
    methods: ["get","head"],
    url: '/admin/sent-emails/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\SentEmailResource\Pages\CreateSentEmail::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/CreateSentEmail.php:7
* @route '/admin/sent-emails/create'
*/
CreateSentEmail.url = (options?: RouteQueryOptions) => {
    return CreateSentEmail.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Resources\SentEmailResource\Pages\CreateSentEmail::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/CreateSentEmail.php:7
* @route '/admin/sent-emails/create'
*/
CreateSentEmail.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateSentEmail.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\SentEmailResource\Pages\CreateSentEmail::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/CreateSentEmail.php:7
* @route '/admin/sent-emails/create'
*/
CreateSentEmail.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: CreateSentEmail.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\SentEmailResource\Pages\CreateSentEmail::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/CreateSentEmail.php:7
* @route '/admin/sent-emails/create'
*/
const CreateSentEmailForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateSentEmail.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\SentEmailResource\Pages\CreateSentEmail::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/CreateSentEmail.php:7
* @route '/admin/sent-emails/create'
*/
CreateSentEmailForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateSentEmail.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\SentEmailResource\Pages\CreateSentEmail::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/CreateSentEmail.php:7
* @route '/admin/sent-emails/create'
*/
CreateSentEmailForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateSentEmail.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

CreateSentEmail.form = CreateSentEmailForm

export default CreateSentEmail