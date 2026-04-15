import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \Spatie\LaravelIgnition\Http\Controllers\UpdateConfigController::__invoke
* @see vendor/spatie/laravel-ignition/src/Http/Controllers/UpdateConfigController.php:10
* @route '/_ignition/update-config'
*/
const UpdateConfigController = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: UpdateConfigController.url(options),
    method: 'post',
})

UpdateConfigController.definition = {
    methods: ["post"],
    url: '/_ignition/update-config',
} satisfies RouteDefinition<["post"]>

/**
* @see \Spatie\LaravelIgnition\Http\Controllers\UpdateConfigController::__invoke
* @see vendor/spatie/laravel-ignition/src/Http/Controllers/UpdateConfigController.php:10
* @route '/_ignition/update-config'
*/
UpdateConfigController.url = (options?: RouteQueryOptions) => {
    return UpdateConfigController.definition.url + queryParams(options)
}

/**
* @see \Spatie\LaravelIgnition\Http\Controllers\UpdateConfigController::__invoke
* @see vendor/spatie/laravel-ignition/src/Http/Controllers/UpdateConfigController.php:10
* @route '/_ignition/update-config'
*/
UpdateConfigController.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: UpdateConfigController.url(options),
    method: 'post',
})

/**
* @see \Spatie\LaravelIgnition\Http\Controllers\UpdateConfigController::__invoke
* @see vendor/spatie/laravel-ignition/src/Http/Controllers/UpdateConfigController.php:10
* @route '/_ignition/update-config'
*/
const UpdateConfigControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: UpdateConfigController.url(options),
    method: 'post',
})

/**
* @see \Spatie\LaravelIgnition\Http\Controllers\UpdateConfigController::__invoke
* @see vendor/spatie/laravel-ignition/src/Http/Controllers/UpdateConfigController.php:10
* @route '/_ignition/update-config'
*/
UpdateConfigControllerForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: UpdateConfigController.url(options),
    method: 'post',
})

UpdateConfigController.form = UpdateConfigControllerForm

export default UpdateConfigController