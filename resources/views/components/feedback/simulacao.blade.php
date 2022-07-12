{{--
    Tarja indicativa de simulação ativa.

    Simulação é o ato de um usuário, geralmente um administrador, utilizar a
    aplicação como se fosse outro usúario.
    Útil para testar a aplicação vendo como ela se comporta através do prisma
    de outro usuário.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<div class="flex font-bold justify-center items-center p-3 space-x-3 alerta">

    <h2>

      {{ __('Simulação ativada por :attribute', ['attribute' => session('simulador')->username]) }}

    </h2>


    @can (\App\Enums\Policy::SimulacaoDelete->value)

      <form>

        @csrf

        @method ('DELETE')


        <x-button
          class='btn-alerta'
          formaction="{{ route('teste.simulacao.destroy') }}"
          formmethod="POST"
          icone="stop-btn"
          :texto="__('Finalizar')"
          :title="__('Finaliza a simulação')"
          type="submit"/>

      </form>

    @endcan

  </div>
