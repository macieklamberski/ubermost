import type { FC } from 'hono/jsx'

export const Footer: FC = () => {
  return (
    <footer class="footer">
      <ul class="footer__links container">
        <li>
          <a href="mailto:hello@ubermost.com" class="anchor">
            hello@ubermost.com
          </a>
        </li>
      </ul>
    </footer>
  )
}
