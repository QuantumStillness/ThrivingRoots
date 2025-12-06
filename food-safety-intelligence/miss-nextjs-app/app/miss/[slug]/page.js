import { supabase } from '@/lib/supabase'
import { notFound } from 'next/navigation'

export default async function IngredientPage({ params, searchParams }) {
  const { slug } = params
  const stickerRef = searchParams.ref // e.g., 'sticker-01-lead'

  // Fetch ingredient details using the RPC function
  const { data, error } = await supabase.rpc('get_ingredient_details', {
    ingredient_slug: slug
  })

  if (error || !data) {
    notFound()
  }

  const ingredient = data

  // Record the sticker scan if ref parameter is present
  if (stickerRef) {
    await supabase.rpc('record_sticker_scan', {
      p_sticker_id: stickerRef,
      p_ingredient_slug: slug
    })
  }

  return (
    <div className="container mx-auto px-4 py-8">
      {/* Hero Section */}
      <div className="bg-yellow-400 border-4 border-black p-8 mb-8">
        <div className="flex items-center gap-4 mb-4">
          {ingredient.pictograms?.map((pictogram, idx) => (
            <img 
              key={idx}
              src={pictogram.image_url} 
              alt={pictogram.name}
              className="w-24 h-24"
            />
          ))}
        </div>
        <h1 className="text-4xl font-bold mb-2">{ingredient.signal_word}: {ingredient.name}</h1>
        <p className="text-xl">{ingredient.description}</p>
      </div>

      {/* Hazard Identification */}
      <section className="mb-8">
        <h2 className="text-3xl font-bold mb-4">Hazard Identification</h2>
        <div className="bg-gray-100 p-6 rounded">
          <h3 className="text-xl font-semibold mb-2">Hazard Statements</h3>
          <ul className="list-disc pl-6 mb-4">
            {ingredient.hazard_statements?.map((statement, idx) => (
              <li key={idx}>{statement}</li>
            ))}
          </ul>
          <h3 className="text-xl font-semibold mb-2">Precautionary Statements</h3>
          <ul className="list-disc pl-6">
            {ingredient.precautionary_statements?.map((statement, idx) => (
              <li key={idx}>{statement}</li>
            ))}
          </ul>
        </div>
      </section>

      {/* Common Sources */}
      <section className="mb-8">
        <h2 className="text-3xl font-bold mb-4">Where It's Found</h2>
        <div className="flex flex-wrap gap-2">
          {ingredient.common_sources?.map((source, idx) => (
            <span key={idx} className="bg-orange-200 px-4 py-2 rounded-full">
              {source}
            </span>
          ))}
        </div>
      </section>

      {/* Health Effects */}
      <section className="mb-8">
        <h2 className="text-3xl font-bold mb-4">Health Effects</h2>
        <p className="text-lg mb-4">{ingredient.health_effects}</p>
        <div className="grid grid-cols-2 gap-4">
          <div className="bg-red-100 p-4 rounded">
            <h3 className="font-semibold">Bioaccumulation Potential</h3>
            <p className="text-2xl font-bold">{ingredient.bioaccumulation_potential}</p>
          </div>
          <div className="bg-yellow-100 p-4 rounded">
            <h3 className="font-semibold">Vulnerable Populations</h3>
            <ul className="list-disc pl-6">
              {ingredient.vulnerable_populations?.map((pop, idx) => (
                <li key={idx}>{pop}</li>
              ))}
            </ul>
          </div>
        </div>
      </section>

      {/* Mindful Consumption Guidance */}
      <section className="mb-8">
        <h2 className="text-3xl font-bold mb-4">Mindful Consumption Guidance</h2>
        <div className="bg-green-100 p-6 rounded mb-4">
          <h3 className="text-xl font-semibold mb-2">Recommended Intake Limit</h3>
          <p className="text-lg">{ingredient.intake_limit}</p>
        </div>
        <div className="bg-blue-100 p-6 rounded mb-4">
          <h3 className="text-xl font-semibold mb-2">Safer Alternatives</h3>
          <ul className="list-disc pl-6">
            {ingredient.mindful_alternatives?.map((alt, idx) => (
              <li key={idx}>{alt}</li>
            ))}
          </ul>
        </div>
        <div className="bg-purple-100 p-6 rounded">
          <h3 className="text-xl font-semibold mb-2">Mindfulness Prompt</h3>
          <p className="italic text-lg">"{ingredient.mindfulness_prompt}"</p>
        </div>
      </section>

      {/* Recall Information */}
      {ingredient.recalls && ingredient.recalls.length > 0 && (
        <section className="mb-8">
          <h2 className="text-3xl font-bold mb-4">Recent Recalls</h2>
          <div className="space-y-4">
            {ingredient.recalls.map((recall, idx) => (
              <div key={idx} className="border-l-4 border-red-500 pl-4">
                <p className="font-semibold">{recall.product_name}</p>
                <p className="text-sm text-gray-600">
                  {recall.recall_date} - {recall.reason} ({recall.severity})
                </p>
                {recall.fda_url && (
                  <a href={recall.fda_url} className="text-blue-600 underline text-sm">
                    View FDA Alert →
                  </a>
                )}
              </div>
            ))}
          </div>
        </section>
      )}

      {/* References */}
      {ingredient.references && ingredient.references.length > 0 && (
        <section className="mb-8">
          <h2 className="text-3xl font-bold mb-4">References</h2>
          <ul className="space-y-2">
            {ingredient.references.map((ref, idx) => (
              <li key={idx}>
                <a href={ref.url} className="text-blue-600 underline">
                  {ref.title}
                </a>
                {ref.author && <span className="text-gray-600"> - {ref.author}</span>}
                {ref.publication_date && (
                  <span className="text-gray-600"> ({new Date(ref.publication_date).getFullYear()})</span>
                )}
              </li>
            ))}
          </ul>
        </section>
      )}
    </div>
  )
}
