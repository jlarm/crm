import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\ReminderResource\Pages\ListReminders::__invoke
* @see app/Filament/Resources/ReminderResource/Pages/ListReminders.php:7
* @route '/admin/reminders'
*/
const ListReminders = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListReminders.url(options),
    method: 'get',
})

ListReminders.definition = {
    methods: ["get","head"],
    url: '/admin/reminders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\ReminderResource\Pages\ListReminders::__invoke
* @see app/Filament/Resources/ReminderResource/Pages/ListReminders.php:7
* @route '/admin/reminders'
*/
ListReminders.url = (options?: RouteQueryOptions) => {
    return ListReminders.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Resources\ReminderResource\Pages\ListReminders::__invoke
* @see app/Filament/Resources/ReminderResource/Pages/ListReminders.php:7
* @route '/admin/reminders'
*/
ListReminders.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListReminders.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\ReminderResource\Pages\ListReminders::__invoke
* @see app/Filament/Resources/ReminderResource/Pages/ListReminders.php:7
* @route '/admin/reminders'
*/
ListReminders.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListReminders.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\ReminderResource\Pages\ListReminders::__invoke
* @see app/Filament/Resources/ReminderResource/Pages/ListReminders.php:7
* @route '/admin/reminders'
*/
const ListRemindersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListReminders.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\ReminderResource\Pages\ListReminders::__invoke
* @see app/Filament/Resources/ReminderResource/Pages/ListReminders.php:7
* @route '/admin/reminders'
*/
ListRemindersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListReminders.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\ReminderResource\Pages\ListReminders::__invoke
* @see app/Filament/Resources/ReminderResource/Pages/ListReminders.php:7
* @route '/admin/reminders'
*/
ListRemindersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListReminders.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListReminders.form = ListRemindersForm

export default ListReminders