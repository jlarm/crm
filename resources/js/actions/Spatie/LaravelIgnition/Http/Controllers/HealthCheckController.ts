import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \Spatie\LaravelIgnition\Http\Controllers\HealthCheckController::__invoke
* @see vendor/spatie/laravel-ignition/src/Http/Controllers/HealthCheckController.php:10
* @route '/_ignition/health-check'
*/
const HealthCheckController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: HealthCheckController.url(options),
    method: 'get',
})

HealthCheckController.definition = {
    methods: ["get","head"],
    url: '/_ignition/health-check',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Spatie\LaravelIgnition\Http\Controllers\HealthCheckController::__invoke
* @see vendor/spatie/laravel-ignition/src/Http/Controllers/HealthCheckController.php:10
* @route '/_ignition/health-check'
*/
HealthCheckController.url = (options?: RouteQueryOptions) => {
    return HealthCheckController.definition.url + queryParams(options)
}

/**
* @see \Spatie\LaravelIgnition\Http\Controllers\HealthCheckController::__invoke
* @see vendor/spatie/laravel-ignition/src/Http/Controllers/HealthCheckController.php:10
* @route '/_ignition/health-check'
*/
HealthCheckController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: HealthCheckController.url(options),
    method: 'get',
})

/**
* @see \Spatie\LaravelIgnition\Http\Controllers\HealthCheckController::__invoke
* @see vendor/spatie/laravel-ignition/src/Http/Controllers/HealthCheckController.php:10
* @route '/_ignition/health-check'
*/
HealthCheckController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: HealthCheckController.url(options),
    method: 'head',
})

/**
* @see \Spatie\LaravelIgnition\Http\Controllers\HealthCheckController::__invoke
* @see vendor/spatie/laravel-ignition/src/Http/Controllers/HealthCheckController.php:10
* @route '/_ignition/health-check'
*/
const HealthCheckControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: HealthCheckController.url(options),
    method: 'get',
})

/**
* @see \Spatie\LaravelIgnition\Http\Controllers\HealthCheckController::__invoke
* @see vendor/spatie/laravel-ignition/src/Http/Controllers/HealthCheckController.php:10
* @route '/_ignition/health-check'
*/
HealthCheckControllerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: HealthCheckController.url(options),
    method: 'get',
})

/**
* @see \Spatie\LaravelIgnition\Http\Controllers\HealthCheckController::__invoke
* @see vendor/spatie/laravel-ignition/src/Http/Controllers/HealthCheckController.php:10
* @route '/_ignition/health-check'
*/
HealthCheckControllerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: HealthCheckController.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

HealthCheckController.form = HealthCheckControllerForm

export default HealthCheckController