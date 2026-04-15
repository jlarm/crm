import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \Filament\Auth\Http\Controllers\LogoutController::__invoke
* @see vendor/filament/filament/src/Auth/Http/Controllers/LogoutController.php:10
* @route '/admin/logout'
*/
const LogoutController0bf9725898bf54069779505e96ede62a = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: LogoutController0bf9725898bf54069779505e96ede62a.url(options),
    method: 'post',
})

LogoutController0bf9725898bf54069779505e96ede62a.definition = {
    methods: ["post"],
    url: '/admin/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \Filament\Auth\Http\Controllers\LogoutController::__invoke
* @see vendor/filament/filament/src/Auth/Http/Controllers/LogoutController.php:10
* @route '/admin/logout'
*/
LogoutController0bf9725898bf54069779505e96ede62a.url = (options?: RouteQueryOptions) => {
    return LogoutController0bf9725898bf54069779505e96ede62a.definition.url + queryParams(options)
}

/**
* @see \Filament\Auth\Http\Controllers\LogoutController::__invoke
* @see vendor/filament/filament/src/Auth/Http/Controllers/LogoutController.php:10
* @route '/admin/logout'
*/
LogoutController0bf9725898bf54069779505e96ede62a.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: LogoutController0bf9725898bf54069779505e96ede62a.url(options),
    method: 'post',
})

/**
* @see \Filament\Auth\Http\Controllers\LogoutController::__invoke
* @see vendor/filament/filament/src/Auth/Http/Controllers/LogoutController.php:10
* @route '/admin/logout'
*/
const LogoutController0bf9725898bf54069779505e96ede62aForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: LogoutController0bf9725898bf54069779505e96ede62a.url(options),
    method: 'post',
})

/**
* @see \Filament\Auth\Http\Controllers\LogoutController::__invoke
* @see vendor/filament/filament/src/Auth/Http/Controllers/LogoutController.php:10
* @route '/admin/logout'
*/
LogoutController0bf9725898bf54069779505e96ede62aForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: LogoutController0bf9725898bf54069779505e96ede62a.url(options),
    method: 'post',
})

LogoutController0bf9725898bf54069779505e96ede62a.form = LogoutController0bf9725898bf54069779505e96ede62aForm
/**
* @see \Filament\Auth\Http\Controllers\LogoutController::__invoke
* @see vendor/filament/filament/src/Auth/Http/Controllers/LogoutController.php:10
* @route '/development/logout'
*/
const LogoutControllerf5694cc0f3cd6d5b36f690d4acc25485 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: LogoutControllerf5694cc0f3cd6d5b36f690d4acc25485.url(options),
    method: 'post',
})

LogoutControllerf5694cc0f3cd6d5b36f690d4acc25485.definition = {
    methods: ["post"],
    url: '/development/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \Filament\Auth\Http\Controllers\LogoutController::__invoke
* @see vendor/filament/filament/src/Auth/Http/Controllers/LogoutController.php:10
* @route '/development/logout'
*/
LogoutControllerf5694cc0f3cd6d5b36f690d4acc25485.url = (options?: RouteQueryOptions) => {
    return LogoutControllerf5694cc0f3cd6d5b36f690d4acc25485.definition.url + queryParams(options)
}

/**
* @see \Filament\Auth\Http\Controllers\LogoutController::__invoke
* @see vendor/filament/filament/src/Auth/Http/Controllers/LogoutController.php:10
* @route '/development/logout'
*/
LogoutControllerf5694cc0f3cd6d5b36f690d4acc25485.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: LogoutControllerf5694cc0f3cd6d5b36f690d4acc25485.url(options),
    method: 'post',
})

/**
* @see \Filament\Auth\Http\Controllers\LogoutController::__invoke
* @see vendor/filament/filament/src/Auth/Http/Controllers/LogoutController.php:10
* @route '/development/logout'
*/
const LogoutControllerf5694cc0f3cd6d5b36f690d4acc25485Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: LogoutControllerf5694cc0f3cd6d5b36f690d4acc25485.url(options),
    method: 'post',
})

/**
* @see \Filament\Auth\Http\Controllers\LogoutController::__invoke
* @see vendor/filament/filament/src/Auth/Http/Controllers/LogoutController.php:10
* @route '/development/logout'
*/
LogoutControllerf5694cc0f3cd6d5b36f690d4acc25485Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: LogoutControllerf5694cc0f3cd6d5b36f690d4acc25485.url(options),
    method: 'post',
})

LogoutControllerf5694cc0f3cd6d5b36f690d4acc25485.form = LogoutControllerf5694cc0f3cd6d5b36f690d4acc25485Form

const LogoutController = {
    '/admin/logout': LogoutController0bf9725898bf54069779505e96ede62a,
    '/development/logout': LogoutControllerf5694cc0f3cd6d5b36f690d4acc25485,
}

export default LogoutController