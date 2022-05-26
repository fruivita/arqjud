{{--
    Main navigation menu.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-menu.group name="{{ __('Functionalities') }}">

    <x-menu.theme-toggler/>


    @auth

        <x-menu.fake-link
            icon="person"
            text="{{ auth()->user()->forHumans() }}"/>


        <x-menu.logout/>

    @else

        <x-menu.link
            class="{{ request()->routeIs('login') ? 'active': '' }}"
            icon="person"
            href="{{ route('login') }}"
            text="{{ __('Login') }}"
            title="{{ __('Go to login page') }}"/>

    @endauth

</x-menu.group>


@auth

    @if (
        auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Box::class)
    )

        <x-menu.group name="{{ __('Register') }}">

            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Box::class)

                <x-menu.link
                    class="{{ request()->routeIs('archiving.register.box.*') ? 'active': '' }}"
                    icon="box2"
                    href="{{ route('archiving.register.box.index') }}"
                    text="{{ __('Boxes') }}"
                    title="{{ __('Boxes management') }}"/>

            @endcan

        </x-menu.group>

    @endif


    @if (
            auth()->user()->can(\App\Enums\Policy::View->value, \App\Models\Configuration::class)
            || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Documentation::class)
            || auth()->user()->can(\App\Enums\Policy::ImportationCreate->value)
            || auth()->user()->can(\App\Enums\Policy::LogViewAny->value)
        )

        <x-menu.group name="{{ __('Administration') }}">

            @can(\App\Enums\Policy::View->value, \App\Models\Configuration::class)

                <x-menu.link
                    class="{{ request()->routeIs('administration.configuration.*') ? 'active': '' }}"
                    icon="gear"
                    href="{{ route('administration.configuration.show') }}"
                    text="{{ __('Configuration') }}"
                    title="{{ __('Application working settings management') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Documentation::class)

                <x-menu.link
                    class="{{ request()->routeIs('administration.doc.*') ? 'active': '' }}"
                    icon="book"
                    href="{{ route('administration.doc.index') }}"
                    text="{{ __('Documentation') }}"
                    title="{{ __('Application routes documentation management') }}"/>

            @endcan


            @can(\App\Enums\Policy::ImportationCreate->value)

                <x-menu.link
                    class="{{ request()->routeIs('administration.importation.*') ? 'active': '' }}"
                    icon="usb-drive"
                    href="{{ route('administration.importation.create') }}"
                    text="{{ __('Importation') }}"
                    title="{{ __('Execution of forced data import') }}"/>

            @endcan


            @can(\App\Enums\Policy::LogViewAny->value)

                <x-menu.link
                    class="{{ request()->routeIs('administration.log.*') ? 'active': '' }}"
                    icon="file-earmark-text"
                    href="{{ route('administration.log.index') }}"
                    text="{{ __('Logs') }}"
                    title="{{ __('Application operation logs management') }}"/>

            @endcan

        </x-menu.group>

    @endif


    @if (
        auth()->user()->can(\App\Enums\Policy::DelegationViewAny->value)
        || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Role::class)
        || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Permission::class)
        || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\User::class)
    )

        <x-menu.group name="{{ __('Authorizations') }}">

            @can(\App\Enums\Policy::DelegationViewAny->value)

                <x-menu.link
                    class="{{ request()->routeIs('authorization.delegations.*') ? 'active': '' }}"
                    icon="person-lines-fill"
                    href="{{ route('authorization.delegations.index') }}"
                    text="{{ __('Delegation') }}"
                    title="{{ __('Roles delegation management') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Role::class)

                <x-menu.link
                    class="{{ request()->routeIs('authorization.role.*') ? 'active': '' }}"
                    icon="award"
                    href="{{ route('authorization.role.index') }}"
                    text="{{ __('Roles') }}"
                    title="{{ __('Application roles management') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Permission::class)

                <x-menu.link
                    class="{{ request()->routeIs('authorization.permission.*') ? 'active': '' }}"
                    icon="vector-pen"
                    href="{{ route('authorization.permission.index') }}"
                    text="{{ __('Permissions') }}"
                    title="{{ __('Application permissions management') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\User::class)

                <x-menu.link
                    class="{{ request()->routeIs('authorization.user.*') ? 'active': '' }}"
                    icon="person-check"
                    href="{{ route('authorization.user.index') }}"
                    text="{{ __('Users') }}"
                    title="{{ __('Users management') }}"/>

            @endcan

        </x-menu.group>

    @endif


    @if (
        auth()->user()->can(\App\Enums\Policy::SimulationCreate->value)
    )

        <x-menu.group name="{{ __('Tests') }}">

            @can(\App\Enums\Policy::SimulationCreate->value)

                <x-menu.link
                    class="{{ request()->routeIs('test.simulation.*') ? 'active': '' }}"
                    icon="people"
                    href="{{ route('test.simulation.create') }}"
                    text="{{ __('Simulation') }}"
                    title="{{ __('Application usage simulation') }}"/>

            @endcan

        </x-menu.group>

    @endif

@endauth
