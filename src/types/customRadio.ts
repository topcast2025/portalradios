export interface RadioRegistrationData {
  name: string
  email: string
  radio_name: string
  stream_url: string
  logo_url?: string
  brief_description: string
  detailed_description?: string
  genres: string[]
  country: string
  language: string
  website?: string
  whatsapp?: string
  facebook?: string
  instagram?: string
  twitter?: string
}

export interface CustomRadio extends RadioRegistrationData {
  id: number
  created_at: string
  total_clicks: number
  status: 'active' | 'pending' | 'inactive'
}

export interface RadioStatistics {
  id: number
  radio_id: number
  access_count: number
  period_start: string
  period_end: string
  last_updated: string
}

export interface RadioError {
  id: number
  radio_id: number
  error_description: string
  user_email?: string
  status: 'pending' | 'resolved' | 'ignored'
  created_at: string
}

// Constantes para os formulários
export const GENRES = [
  '20s', '30s', '40s', '50s', '60s', '70s', '80s', '90s',
  'African', 'African Music', 'Afrobeat', 'Alternative', 'Ambient',
  'Arabesque', 'Arabic', 'Asian', 'Austro', 'Bachata', 'Ballads',
  'Bebop', 'Big Band', 'Bluegrass', 'Blues', 'Bollywood', 'Bossa Nova',
  'Celtic', 'Chanson', 'Chillout', 'Christian Contemporary', 'Christian Music',
  'Classic Rock', 'Classicas', 'Country', 'Cumbia', 'Dancehall', 'Deep House',
  'Disco', 'Discofox', "Drum'n'Bass", 'Dub', 'Electro', 'Electronica',
  'Esportes', 'Eurodance', 'Fado', 'Film & Musical', 'FlashBack', 'Folk',
  'Folklore', 'Forró', 'Funk', 'Garage Rock', 'German Folklore', 'Gospel',
  'Gothic', 'Grime', 'Hard Rock', 'Hardcore', 'Hardstyle', 'Heavy Metal',
  'Hip Hop', 'HipHop', 'Hits', 'House', 'Indian Music', 'Indie', 'Industrial',
  'Instrumental', 'Islamic music', 'Italian Music', 'J-pop', 'Jazz', 'Jungle',
  'K-Pop', 'Kizomba', 'Latin', 'Live Music', 'Lounge', 'Manele', 'Merengue',
  'Metal', 'Metalcore', 'Minimal', 'Motown', 'Musical', 'Musicas Brasileiras',
  'Musicas Suaves', 'Neo-Medieval', 'New Wave', 'News-Talk', 'Noticias',
  'Oldies', 'Opera', 'Oriental', 'Podcast', 'Pop', 'Progressive House',
  'Punk', "R'n'B", 'Ragga', 'Ranchera', 'Rap', 'Reggae', 'Reggaeton',
  'Rock', "Rock'n'Roll", 'Rumba', 'Salsa', 'Samba', 'Schlager', 'Sea shanty',
  'Sertanejo', 'Ska', 'Soft Rock', 'Soul', 'Swing', 'Talk', 'Tamil', 'Tango',
  'Techno', 'Top 40', 'Traditional', 'Traditional music', 'Trance', 'Trap',
  'Urban', 'World', 'Zouk and Tropical'
]

export const COUNTRIES = [
  'Afghanistan', 'Albania', 'Alemanha', 'Algeria', 'Angola', 'Antigua and Barbuda',
  'Argentina', 'Aruba', 'Australia', 'Austria', 'Azerbaijan', 'Bahrain',
  'Barbados', 'Belarus', 'Belgium', 'Belize', 'Bolivia', 'Bosnia and Herzegovina',
  'Brasil', 'Brunei', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cameroon',
  'Canada', 'Cape Verde', 'Chile', 'China', 'Colombia', 'Congo', 'Costa Rica',
  'Croatia', 'Cuba', 'Curacao', 'Cyprus', 'Czech Republic', 'Denmark',
  'Dominica', 'Dominican Republic', 'East Timor', 'Ecuador', 'Egypt',
  'El Salvador', 'Estonia', 'Ethiopia', 'Finland', 'France', 'Gabon',
  'Gambia', 'Georgia', 'Ghana', 'Greece', 'Guatemala', 'Guinea', 'Haiti',
  'Honduras', 'Hong Kong', 'Hungary', 'Iceland', 'India', 'Indonesia',
  'Iran', 'Iraq', 'Ireland', 'Israel', 'Italia', 'Ivory Coast', 'Jamaica',
  'Japan', 'Kazakhstan', 'Kenya', 'Kosovo', 'Latvia', 'Lebanon', 'Lesotho',
  'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macedonia',
  'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta',
  'Mauritius', 'Mexico', 'Moldova', 'Monaco', 'Montenegro', 'Morocco',
  'Mozambique', 'Namibia', 'Nepal', 'Netherlands', 'New Zealand',
  'Nicaragua', 'Nigeria', 'Norway', 'Oman', 'Overseas France', 'Pakistan',
  'Panama', 'Paraguay', 'Peru', 'Philippines', 'Poland', 'Portugal',
  'Puerto Rico', 'Qatar', 'Republic of China (Taiwan)', 'Republic of Guinea-Bissau',
  'Romania', 'Russia', 'Rwanda', 'Saint Lucia', 'Saint Vincent and the Grenadines',
  'San Marino', 'Saudi Arabia', 'Senegal', 'Serbia', 'Seychelles',
  'Singapore', 'Slovakia', 'Slovenia', 'Somalia', 'South Africa',
  'South Korea', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Sweden',
  'Switzerland', 'Syria', 'Tanzania', 'Thailand', 'The Bahamas', 'Togo',
  'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Uganda', 'Ukraine',
  'United Arab Emirates', 'United Kingdom', 'Uruguay', 'USA', 'Uzbekistan',
  'Vatican City State', 'Venezuela', 'Zambia', 'Zimbabwe'
]

export const LANGUAGES = [
  'portuguese', 'english', 'spanish', 'french', 'german', 'italian',
  'russian', 'chinese', 'japanese', 'korean', 'arabic', 'hindi',
  'dutch', 'swedish', 'norwegian', 'danish', 'finnish', 'polish',
  'czech', 'hungarian', 'romanian', 'bulgarian', 'greek', 'turkish',
  'hebrew', 'thai', 'vietnamese', 'indonesian', 'malay', 'tagalog'
]