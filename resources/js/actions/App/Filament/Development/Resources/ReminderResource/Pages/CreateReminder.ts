import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../../wayfinder'
/**
* @see \App\Filament\Development\Resources\ReminderResource\Pages\CreateReminder::__invoke
* @see app/Filament/Development/Resources/ReminderResource/Pages/CreateReminder.php:7
* @route '/development/reminders/create'
*/
const CreateReminder = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateReminder.url(options),
    method: 'get',
})

CreateReminder.definition = {
    methods: ["get","head"],
    url: '/development/reminders/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Development\Resources\ReminderResource\Pages\CreateReminder::__invoke
* @see app/Filament/Development/Resources/ReminderResource/Pages/CreateReminder.php:7
* @route '/development/reminders/create'
*/
CreateReminder.url = (options?: RouteQueryOptions) => {
    return CreateReminder.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Development\Resources\ReminderResource\Pages\CreateReminder::__invoke
* @see app/Filament/Development/Resources/ReminderResource/Pages/CreateReminder.php:7
* @route '/development/reminders/create'
*/
CreateReminder.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateReminder.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Development\Resources\ReminderResource\Pages\CreateReminder::__invoke
* @see app/Filament/Development/Resources/ReminderResource/Pages/CreateReminder.php:7
* @route '/development/reminders/create'
*/
CreateReminder.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: CreateReminder.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Development\Resources\ReminderResource\Pages\CreateReminder::__invoke
* @see app/Filament/Development/Resources/ReminderResource/Pages/CreateReminder.php:7
* @route '/development/reminders/create'
*/
const CreateReminderForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateReminder.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Development\Resources\ReminderResource\Pages\CreateReminder::__invoke
* @see app/Filament/Development/Resources/ReminderResource/Pages/CreateReminder.php:7
* @route '/development/reminders/create'
*/
CreateReminderForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateReminder.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Development\Resources\ReminderResource\Pages\CreateReminder::__invoke
* @see app/Filament/Development/Resources/ReminderResource/Pages/CreateReminder.php:7
* @route '/development/reminders/create'
*/
CreateReminderForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateReminder.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

CreateReminder.form = CreateReminderForm

export default CreateReminder