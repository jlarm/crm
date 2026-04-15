import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\ReminderResource\Pages\EditReminder::__invoke
* @see app/Filament/Resources/ReminderResource/Pages/EditReminder.php:7
* @route '/admin/reminders/{record}/edit'
*/
const EditReminder = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditReminder.url(args, options),
    method: 'get',
})

EditReminder.definition = {
    methods: ["get","head"],
    url: '/admin/reminders/{record}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\ReminderResource\Pages\EditReminder::__invoke
* @see app/Filament/Resources/ReminderResource/Pages/EditReminder.php:7
* @route '/admin/reminders/{record}/edit'
*/
EditReminder.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return EditReminder.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Filament\Resources\ReminderResource\Pages\EditReminder::__invoke
* @see app/Filament/Resources/ReminderResource/Pages/EditReminder.php:7
* @route '/admin/reminders/{record}/edit'
*/
EditReminder.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditReminder.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\ReminderResource\Pages\EditReminder::__invoke
* @see app/Filament/Resources/ReminderResource/Pages/EditReminder.php:7
* @route '/admin/reminders/{record}/edit'
*/
EditReminder.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: EditReminder.url(args, options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\ReminderResource\Pages\EditReminder::__invoke
* @see app/Filament/Resources/ReminderResource/Pages/EditReminder.php:7
* @route '/admin/reminders/{record}/edit'
*/
const EditReminderForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditReminder.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\ReminderResource\Pages\EditReminder::__invoke
* @see app/Filament/Resources/ReminderResource/Pages/EditReminder.php:7
* @route '/admin/reminders/{record}/edit'
*/
EditReminderForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditReminder.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\ReminderResource\Pages\EditReminder::__invoke
* @see app/Filament/Resources/ReminderResource/Pages/EditReminder.php:7
* @route '/admin/reminders/{record}/edit'
*/
EditReminderForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditReminder.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

EditReminder.form = EditReminderForm

export default EditReminder