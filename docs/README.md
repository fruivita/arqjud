# ArqJud: Arquivo Judicial

[![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/fruivita/arqjud?logo=github)](/../../releases)
[![GitHub Release Date](https://img.shields.io/github/release-date/fruivita/arqjud?logo=github)](/../../releases)
[![GitHub last commit (branch)](https://img.shields.io/github/last-commit/fruivita/arqjud/main?logo=github)](/../../commits/main)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/fruivita/arqjud/tests.yml?branch=main&label=tests)](/../../actions/workflows/tests.yml?query=branch%3Amain)
[![Test Coverage](https://api.codeclimate.com/v1/badges/e331b0511b37da03e24d/test_coverage)](https://codeclimate.com/github/fruivita/arqjud/test_coverage)
[![Maintainability](https://api.codeclimate.com/v1/badges/e331b0511b37da03e24d/maintainability)](https://codeclimate.com/github/fruivita/arqjud/maintainability)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/fruivita/arqjud/static.yml?branch=main&label=static)](/../../actions/workflows/static.yml?query=branch%3Amain)
[![GitHub issues](https://img.shields.io/github/issues/fruivita/arqjud?logo=github)](/../../issues)
![GitHub repo size](https://img.shields.io/github/repo-size/fruivita/arqjud?logo=github)
[![GitHub](https://img.shields.io/github/license/fruivita/arqjud?logo=github)](../LICENSE.md)

Gerenciamento de processos judiciais arquivados.

Esta aplicação foi planejada e desenvolvida de acordo com as necessidades e regras de negócios da Justiça Federal do Espírito Santo. Contudo, ela pode ser utilizada em outros órgãos observados os termos previstos no [licenciamento](#license).

&nbsp;

---

## Table of Contents

1. [Notes](#notes)

2. [Prerequisites](#prerequisites)

3. [Testing and Continuous Integration](#testing-and-continuous-integration)

4. [Changelog](#changelog)

5. [Contributing](#contributing)

6. [Code of conduct](#code-of-conduct)

7. [Security Vulnerabilities](#security-vulnerabilities)

8. [Support and Updates](#support-and-updates)

9. [Roadmap](#roadmap)

10. [Credits](#credits)

11. [License](#license)

---

## Notes

Basicamente, a aplicação gerencia a localização do processos arquivados de acordo com duas regras básicas:

1. Processo **dentro** do setor responsável pelo arquivamento

    A aplicação informa a localização onde o processo está arquivado ou para onde ele deve voltar.

    &nbsp;

2. Processo **fora** do setor responsável pelo arquivamento

    A aplicação informa para onde o processo foi remetido.

    &nbsp;

Para informações completas e detalhadas, consulte a [wiki](/../../wiki) da aplicação.

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Prerequisites

1. Dependências PHP

    PHP ^8.0

    [Extensões](https://getcomposer.org/doc/03-cli.md#check-platform-reqs)

    ```bash
    composer check-platform-reqs
    ```

2. Bando de dados

    MySQL 8

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Testing and Continuous Integration

```bash
composer analyse
composer test
composer coverage
```

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Changelog

Por favor, veja o [CHANGELOG](CHANGELOG.md) para maiores informações sobre o que mudou em cada versão.

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Contributing

Por favor, veja [CONTRIBUTING](CONTRIBUTING.md) para maiores detalhes sobre como contribuir.

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Code of conduct

Para garantir que todos sejam bem vindos a contribuir com este projeto open-source, por favor leia e siga o [Código de Conduta](CODE_OF_CONDUCT.md).

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Security Vulnerabilities

Por favor, veja na [política de segurança](/../../security/policy) como reportar vulnerabilidades ou falhas de segurança.

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Support and Updates

A versão mais recente receberá suporte e atualizações sempre que houver necessidade. As demais, receberão atualizações por 06 meses após terem sido substituídas por uma nova versão sendo, então, descontinuadas.

| Version | PHP     | MySql      | Release    | End of Life |
|---------|---------|------------|------------|-------------|
| 1.0     | ^8.0    | 8          | dd-mm-yyyy | dd-mm-yyyy  |

🐛 Encontrou um bug?!?! Abra um **[issue](/../../issues/new?assignees=fcno&labels=bug%2Ctriage&template=bug_report.yml&title=%5BT%C3%ADtulo+conciso+do+bug%5D)**.

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Roadmap

> ✨ Alguma ideia nova?!?! Inicie **[uma discussão](https://github.com/orgs/fruivita/discussions/new?category=ideas&title=[ArqJud])**.

A lista a seguir contém as necessidades de melhorias identificadas e aprovadas que serão implementadas na primeira janela de oportunidade.

- [ ] n/a

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Credits

- [Fábio Cassiano](https://github.com/fcno)

- [All Contributors](/../../contributors)

⬆️ [Voltar](#table-of-contents)

&nbsp;

## License

The MIT License (MIT). Por favor, veja o **[License File](../LICENSE.md)** para maiores informações.

⬆️ [Voltar](#table-of-contents)
