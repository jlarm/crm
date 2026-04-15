import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \Filament\Auth\Pages\Login::__invoke
* @see vendor/filament/filament/src/Auth/Pages/Login.php:7
* @route '/admin/login'
*/
const Login047f8ce2fdeb7128b2677a1dd45b96b8 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Login047f8ce2fdeb7128b2677a1dd45b96b8.url(options),
    method: 'get',
})

Login047f8ce2fdeb7128b2677a1dd45b96b8.definition = {
    methods: ["get","head"],
    url: '/admin/login',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Filament\Auth\Pages\Login::__invoke
* @see vendor/filament/filament/src/Auth/Pages/Login.php:7
* @route '/admin/login'
*/
Login047f8ce2fdeb7128b2677a1dd45b96b8.url = (options?: RouteQueryOptions) => {
    return Login047f8ce2fdeb7128b2677a1dd45b96b8.definition.url + queryParams(options)
}

/**
* @see \Filament\Auth\Pages\Login::__invoke
* @see vendor/filament/filament/src/Auth/Pages/Login.php:7
* @route '/admin/login'
*/
Login047f8ce2fdeb7128b2677a1dd45b96b8.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Login047f8ce2fdeb7128b2677a1dd45b96b8.url(options),
    method: 'get',
})

/**
* @see \Filament\Auth\Pages\Login::__invoke
* @see vendor/filament/filament/src/Auth/Pages/Login.php:7
* @route '/admin/login'
*/
Login047f8ce2fdeb7128b2677a1dd45b96b8.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Login047f8ce2fdeb7128b2677a1dd45b96b8.url(options),
    method: 'head',
})

/**
* @see \Filament\Auth\Pages\Login::__invoke
* @see vendor/filament/filament/src/Auth/Pages/Login.php:7
* @route '/admin/login'
*/
const Login047f8ce2fdeb7128b2677a1dd45b96b8Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Login047f8ce2fdeb7128b2677a1dd45b96b8.url(options),
    method: 'get',
})

/**
* @see \Filament\Auth\Pages\Login::__invoke
* @see vendor/filament/filament/src/Auth/Pages/Login.php:7
* @route '/admin/login'
*/
Login047f8ce2fdeb7128b2677a1dd45b96b8Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Login047f8ce2fdeb7128b2677a1dd45b96b8.url(options),
    method: 'get',
})

/**
* @see \Filament\Auth\Pages\Login::__invoke
* @see vendor/filament/filament/src/Auth/Pages/Login.php:7
* @route '/admin/login'
*/
Login047f8ce2fdeb7128b2677a1dd45b96b8Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Login047f8ce2fdeb7128b2677a1dd45b96b8.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Login047f8ce2fdeb7128b2677a1dd45b96b8.form = Login047f8ce2fdeb7128b2677a1dd45b96b8Form
/**
* @see \Filament\Auth\Pages\Login::__invoke
* @see vendor/filament/filament/src/Auth/Pages/Login.php:7
* @route '/development/login'
*/
const Loginc97679292df6c02d50b42b7d3e0b0736 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Loginc97679292df6c02d50b42b7d3e0b0736.url(options),
    method: 'get',
})

Loginc97679292df6c02d50b42b7d3e0b0736.definition = {
    methods: ["get","head"],
    url: '/development/login',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Filament\Auth\Pages\Login::__invoke
* @see vendor/filament/filament/src/Auth/Pages/Login.php:7
* @route '/development/login'
*/
Loginc97679292df6c02d50b42b7d3e0b0736.url = (options?: RouteQueryOptions) => {
    return Loginc97679292df6c02d50b42b7d3e0b0736.definition.url + queryParams(options)
}

/**
* @see \Filament\Auth\Pages\Login::__invoke
* @see vendor/filament/filament/src/Auth/Pages/Login.php:7
* @route '/development/login'
*/
Loginc97679292df6c02d50b42b7d3e0b0736.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Loginc97679292df6c02d50b42b7d3e0b0736.url(options),
    method: 'get',
})

/**
* @see \Filament\Auth\Pages\Login::__invoke
* @see vendor/filament/filament/src/Auth/Pages/Login.php:7
* @route '/development/login'
*/
Loginc97679292df6c02d50b42b7d3e0b0736.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Loginc97679292df6c02d50b42b7d3e0b0736.url(options),
    method: 'head',
})

/**
* @see \Filament\Auth\Pages\Login::__invoke
* @see vendor/filament/filament/src/Auth/Pages/Login.php:7
* @route '/development/login'
*/
const Loginc97679292df6c02d50b42b7d3e0b0736Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Loginc97679292df6c02d50b42b7d3e0b0736.url(options),
    method: 'get',
})

/**
* @see \Filament\Auth\Pages\Login::__invoke
* @see vendor/filament/filament/src/Auth/Pages/Login.php:7
* @route '/development/login'
*/
Loginc97679292df6c02d50b42b7d3e0b0736Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Loginc97679292df6c02d50b42b7d3e0b0736.url(options),
    method: 'get',
})

/**
* @see \Filament\Auth\Pages\Login::__invoke
* @see vendor/filament/filament/src/Auth/Pages/Login.php:7
* @route '/development/login'
*/
Loginc97679292df6c02d50b42b7d3e0b0736Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Loginc97679292df6c02d50b42b7d3e0b0736.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Loginc97679292df6c02d50b42b7d3e0b0736.form = Loginc97679292df6c02d50b42b7d3e0b0736Form

const Login = {
    '/admin/login': Login047f8ce2fdeb7128b2677a1dd45b96b8,
    '/development/login': Loginc97679292df6c02d50b42b7d3e0b0736,
}

export default Login