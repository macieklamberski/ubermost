import type { PageView } from 'mikrob'
import site from '../../data/site.json'
import { Layout } from '../partials/Layout'

export const ErrorPage: PageView = ({ context }) => {
  const title = `Not found — ${site.title}`
  const description = site.description
  const content = (
    <section class="post">
      <p class="post__copy copy container">The URL you requested was stolen from the internet.</p>
    </section>
  )

  context.status(404)

  return <Layout title={title} description={description} content={content} />
}

export default ErrorPage
