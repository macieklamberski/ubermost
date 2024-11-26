import type { FC } from 'hono/jsx'
import { Logo } from './Logo'
import { Copy } from './Copy'

export type HeaderProps = {
  about: string
}

export const Header: FC<HeaderProps> = ({ about }) => {
  return (
    <header class="header container">
      <h1 class="header__logo">
        <a href="/">
          <Logo />
        </a>
      </h1>
      <div class="header__about">
        <Copy body={about} />
      </div>
    </header>
  )
}
