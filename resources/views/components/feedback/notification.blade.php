{{--
    Feedback in the form of a popup notification to respond to the user's
    request.
    Suitable for when 'inline' feedback is not indicated.

    After a certain time, the message will be automatically extinguished.

    The component waits for the 'notify' event to be emitted accompanied by:
    - message type (error or success)
    - representative image icon
    - header
    - message
    - message timeout

    Note: This is an intrusive display component, as it overlays the page
    content.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}

<div
    x-data="{
        notifications : [],
        remove(notification) {
            this.notifications.splice(this.notifications.indexOf(notification), 1)
        },
    }"
    x-on:notify.window="
        notification = $event.detail;
        notification.timeout = $event.detail.timeout ?? 2500;
        notification.timeout_id = setTimeout(() => {
            remove(notification)
        }, notification.timeout);
        notifications.push(notification);
    "
    x-transition.duration.500ms
    class="top-12 fixed right-3 z-30 space-y-3"
>

    <template x-for="(notification, notificationIndex) in notifications" :key="notificationIndex">

        <div
            x-bind:class="notification.type"
            x-on:mouseover.once="clearTimeout(notification.timeout_id)"
            class="border-l-8 border-r-8 p-3"
        >

            <div class="flex items-center space-x-3">

                {{-- message context icon --}}
                <div x-html="notification.icon"></div>


                <div x-bind:class="(notification.header && notification.message) ? 'space-y-3' : ''">

                    {{-- message header --}}
                    <h3 x-text="notification.header" class="font-bold text-lg"></h3>


                    {{-- message itself --}}
                    <p class="text-center" x-text="notification.message"></p>

                </div>

                    {{-- button to close the dialog box --}}
                    <button x-on:click="remove(notification)" class="animate-none lg:animate-ping" id="btn-flash" type="button">

                        <x-icon name="x-circle"/>

                    </button>

                </div>

            </div>

        </div>

    </template>

</div>
