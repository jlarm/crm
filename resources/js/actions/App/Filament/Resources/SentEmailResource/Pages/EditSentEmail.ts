import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\SentEmailResource\Pages\EditSentEmail::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/EditSentEmail.php:7
* @route '/admin/sent-emails/{record}/edit'
*/
const EditSentEmail = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditSentEmail.url(args, options),
    method: 'get',
})

EditSentEmail.definition = {
    methods: ["get","head"],
    url: '/admin/sent-emails/{record}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\SentEmailResource\Pages\EditSentEmail::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/EditSentEmail.php:7
* @route '/admin/sent-emails/{record}/edit'
*/
EditSentEmail.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return EditSentEmail.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Filament\Resources\SentEmailResource\Pages\EditSentEmail::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/EditSentEmail.php:7
* @route '/admin/sent-emails/{record}/edit'
*/
EditSentEmail.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditSentEmail.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\SentEmailResource\Pages\EditSentEmail::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/EditSentEmail.php:7
* @route '/admin/sent-emails/{record}/edit'
*/
EditSentEmail.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: EditSentEmail.url(args, options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\SentEmailResource\Pages\EditSentEmail::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/EditSentEmail.php:7
* @route '/admin/sent-emails/{record}/edit'
*/
const EditSentEmailForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditSentEmail.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\SentEmailResource\Pages\EditSentEmail::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/EditSentEmail.php:7
* @route '/admin/sent-emails/{record}/edit'
*/
EditSentEmailForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditSentEmail.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\SentEmailResource\Pages\EditSentEmail::__invoke
* @see app/Filament/Resources/SentEmailResource/Pages/EditSentEmail.php:7
* @route '/admin/sent-emails/{record}/edit'
*/
EditSentEmailForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditSentEmail.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

EditSentEmail.form = EditSentEmailForm

export default EditSentEmail