const express = require('express');
const session = require('express-session');
const multer = require('multer');
const path = require('path');
const fs = require('fs');

const app = express();
const PORT = process.env.PORT || 3000;

// Paths
const DATA_DIR = path.join(__dirname, 'data');
const DATA_FILE = path.join(DATA_DIR, 'menu.json');
const UPLOADS_DIR = path.join(__dirname, 'uploads');

// ── Middleware ──────────────────────────────────────────────────────────────
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(session({
  secret: 'restaurant-menu-secret-key',
  resave: false,
  saveUninitialized: false,
  cookie: { maxAge: 24 * 60 * 60 * 1000 } // 24 hours
}));

// Serve static files
app.use(express.static(__dirname));
app.use('/uploads', express.static(UPLOADS_DIR));

// ── Helpers ────────────────────────────────────────────────────────────────

function readMenuData() {
  try {
    const content = fs.readFileSync(DATA_FILE, 'utf-8');
    const data = JSON.parse(content);
    return data;
  } catch {
    return null;
  }
}

function writeMenuData(data) {
  try {
    const dir = path.dirname(DATA_FILE);
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
    }
    fs.writeFileSync(DATA_FILE, JSON.stringify(data, null, 4), 'utf-8');
    return true;
  } catch {
    return false;
  }
}

function getMaxId(items) {
  if (!items || items.length === 0) return 0;
  return Math.max(...items.map(item => item.id || 0));
}

// Auth middleware
function requireAuth(req, res, next) {
  if (!req.session.admin_logged_in) {
    return res.status(401).json({ success: false, error: '未授权访问，请先登录' });
  }
  next();
}

// ── API Routes ─────────────────────────────────────────────────────────────

// Login
app.post('/api/login', (req, res) => {
  const { username, password } = req.body;
  if (username === 'admin' && password === 'admin123') {
    req.session.admin_logged_in = true;
    req.session.admin_username = 'admin';
    return res.json({ success: true });
  }
  return res.status(401).json({ success: false, error: '用户名或密码错误' });
});

// Logout
app.post('/api/logout', (req, res) => {
  req.session.destroy();
  return res.json({ success: true });
});

// Check auth status
app.get('/api/auth-check', (req, res) => {
  return res.json({ loggedIn: !!req.session.admin_logged_in });
});

// List menu (public, no auth needed)
app.get('/api/menu', (req, res) => {
  const data = readMenuData();
  if (!data) {
    return res.json({ success: false, error: '无法读取菜单数据' });
  }
  return res.json({ success: true, data });
});

// ── Dish Routes ────────────────────────────────────────────────────────────

// Add dish
app.post('/api/dish', requireAuth, (req, res) => {
  const data = readMenuData();
  if (!data) return res.json({ success: false, error: '无法读取菜单数据' });

  const { name, name_id, price, category_id, sort, image } = req.body;

  if (!name || !name.trim()) {
    return res.json({ success: false, error: '菜品名称不能为空' });
  }
  if (!price || price <= 0) {
    return res.json({ success: false, error: '价格必须大于 0' });
  }
  if (!category_id) {
    return res.json({ success: false, error: '必须选择分类' });
  }

  const categoryExists = data.categories.some(cat => cat.id === category_id);
  if (!categoryExists) {
    return res.json({ success: false, error: '分类不存在' });
  }

  const newId = getMaxId(data.dishes) + 1;
  let dishSort = sort || 0;

  if (dishSort <= 0) {
    const maxSort = data.dishes
      .filter(d => d.category_id === category_id)
      .reduce((max, d) => Math.max(max, d.sort || 0), 0);
    dishSort = maxSort + 1;
  }

  const newDish = {
    id: newId,
    category_id,
    name: name.trim(),
    name_id: (name_id || '').trim(),
    price: parseInt(price),
    image: image || '',
    sort: dishSort
  };

  data.dishes.push(newDish);

  if (writeMenuData(data)) {
    return res.json({ success: true, data: newDish });
  }
  return res.json({ success: false, error: '保存失败' });
});

// Edit dish
app.put('/api/dish/:id', requireAuth, (req, res) => {
  const data = readMenuData();
  if (!data) return res.json({ success: false, error: '无法读取菜单数据' });

  const id = parseInt(req.params.id);
  if (id <= 0) return res.json({ success: false, error: '无效的菜品 ID' });

  const dish = data.dishes.find(d => d.id === id);
  if (!dish) return res.json({ success: false, error: '菜品不存在' });

  const { name, name_id, price, category_id, sort, image } = req.body;
  if (name !== undefined) dish.name = name.trim();
  if (name_id !== undefined) dish.name_id = name_id.trim();
  if (price !== undefined) dish.price = parseInt(price);
  if (category_id !== undefined) dish.category_id = category_id;
  if (sort !== undefined) dish.sort = parseInt(sort);
  if (image !== undefined) dish.image = image;

  if (writeMenuData(data)) {
    return res.json({ success: true, message: '菜品已更新' });
  }
  return res.json({ success: false, error: '保存失败' });
});

// Delete dish
app.delete('/api/dish/:id', requireAuth, (req, res) => {
  const data = readMenuData();
  if (!data) return res.json({ success: false, error: '无法读取菜单数据' });

  const id = parseInt(req.params.id);
  if (id <= 0) return res.json({ success: false, error: '无效的菜品 ID' });

  const dish = data.dishes.find(d => d.id === id);
  if (!dish) return res.json({ success: false, error: '菜品不存在' });

  // Delete image file if exists
  if (dish.image) {
    const imagePath = path.join(UPLOADS_DIR, dish.image);
    if (fs.existsSync(imagePath)) {
      try { fs.unlinkSync(imagePath); } catch {}
    }
  }

  data.dishes = data.dishes.filter(d => d.id !== id);

  if (writeMenuData(data)) {
    return res.json({ success: true, message: '菜品已删除' });
  }
  return res.json({ success: false, error: '删除失败' });
});

// ── Category Routes ────────────────────────────────────────────────────────

// Add category
app.post('/api/category', requireAuth, (req, res) => {
  const data = readMenuData();
  if (!data) return res.json({ success: false, error: '无法读取菜单数据' });

  const { name, name_id } = req.body;

  if (!name || !name.trim()) {
    return res.json({ success: false, error: '分类名称不能为空' });
  }

  // Generate ID from name (replace non-alphanumeric with hyphens)
  let id = name.trim().toLowerCase().replace(/[^a-z0-9]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
  if (!id) id = 'category';

  // Ensure unique ID
  let baseId = id;
  let counter = 1;
  while (data.categories.some(cat => cat.id === id)) {
    id = baseId + '-' + counter;
    counter++;
  }

  // Calculate sort order
  let sort = 1;
  data.categories.forEach(cat => {
    if (cat.sort >= sort) sort = cat.sort + 1;
  });

  const newCategory = {
    id,
    name: name.trim(),
    name_id: (name_id || '').trim(),
    sort
  };

  data.categories.push(newCategory);

  if (writeMenuData(data)) {
    return res.json({ success: true, data: newCategory });
  }
  return res.json({ success: false, error: '保存失败' });
});

// Edit category
app.put('/api/category/:id', requireAuth, (req, res) => {
  const data = readMenuData();
  if (!data) return res.json({ success: false, error: '无法读取菜单数据' });

  const catId = req.params.id;
  if (!catId) return res.json({ success: false, error: '无效的分类 ID' });

  const category = data.categories.find(c => c.id === catId);
  if (!category) return res.json({ success: false, error: '分类不存在' });

  const { name, name_id, sort } = req.body;

  if (name !== undefined) {
    if (!name.trim()) return res.json({ success: false, error: '分类名称不能为空' });
    category.name = name.trim();
  }
  if (name_id !== undefined) category.name_id = name_id.trim();
  if (sort !== undefined) category.sort = parseInt(sort);

  if (writeMenuData(data)) {
    return res.json({ success: true, message: '分类已更新' });
  }
  return res.json({ success: false, error: '保存失败' });
});

// Delete category
app.delete('/api/category/:id', requireAuth, (req, res) => {
  const data = readMenuData();
  if (!data) return res.json({ success: false, error: '无法读取菜单数据' });

  const catId = req.params.id;
  if (!catId) return res.json({ success: false, error: '无效的分类 ID' });

  const categoryExists = data.categories.some(c => c.id === catId);
  if (!categoryExists) return res.json({ success: false, error: '分类不存在' });

  // Delete all dishes in this category (and their images)
  data.dishes.forEach(dish => {
    if (dish.category_id === catId && dish.image) {
      const imagePath = path.join(UPLOADS_DIR, dish.image);
      if (fs.existsSync(imagePath)) {
        try { fs.unlinkSync(imagePath); } catch {}
      }
    }
  });

  data.dishes = data.dishes.filter(d => d.category_id !== catId);
  data.categories = data.categories.filter(c => c.id !== catId);

  if (writeMenuData(data)) {
    return res.json({ success: true, message: '分类及该分类下所有菜品已删除' });
  }
  return res.json({ success: false, error: '删除失败' });
});

// ── Reorder ────────────────────────────────────────────────────────────────

app.post('/api/reorder', requireAuth, (req, res) => {
  const data = readMenuData();
  if (!data) return res.json({ success: false, error: '无法读取菜单数据' });

  const { type, items } = req.body;
  if (!type || !items || !items.length) {
    return res.json({ success: false, error: '参数错误' });
  }

  if (type === 'categories') {
    items.forEach(item => {
      const cat = data.categories.find(c => c.id === item.id);
      if (cat) cat.sort = parseInt(item.sort);
    });
    data.categories.sort((a, b) => a.sort - b.sort);
  } else if (type === 'dishes') {
    items.forEach(item => {
      const dish = data.dishes.find(d => d.id === parseInt(item.id));
      if (dish) dish.sort = parseInt(item.sort);
    });
    data.dishes.sort((a, b) => {
      if (a.category_id !== b.category_id) {
        return a.category_id.localeCompare(b.category_id);
      }
      return a.sort - b.sort;
    });
  } else {
    return res.json({ success: false, error: '无效的排序类型' });
  }

  if (writeMenuData(data)) {
    return res.json({ success: true, message: '排序已更新' });
  }
  return res.json({ success: false, error: '保存失败' });
});

// ── Image Upload ───────────────────────────────────────────────────────────

const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    if (!fs.existsSync(UPLOADS_DIR)) {
      fs.mkdirSync(UPLOADS_DIR, { recursive: true });
    }
    cb(null, UPLOADS_DIR);
  },
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname).toLowerCase();
    const uniqueName = 'dish_' + Date.now() + '_' + Math.random().toString(36).substring(2, 8) + ext;
    cb(null, uniqueName);
  }
});

const upload = multer({
  storage,
  limits: { fileSize: 5 * 1024 * 1024 }, // 5MB
  fileFilter: (req, file, cb) => {
    const allowedExts = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
    const allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const ext = path.extname(file.originalname).toLowerCase();
    const mime = file.mimetype;

    if (allowedExts.includes(ext) && allowedMimes.includes(mime)) {
      cb(null, true);
    } else {
      cb(new Error('不支持的文件类型，仅支持 JPG、PNG、GIF、WebP'));
    }
  }
});

app.post('/api/upload', requireAuth, upload.single('image'), (req, res) => {
  if (!req.file) {
    return res.json({ success: false, error: '未选择图片文件' });
  }

  return res.json({
    success: true,
    filename: req.file.filename,
    original_name: req.file.originalname,
    size: req.file.size,
    type: req.file.mimetype
  });
});

// Multer error handler
app.use('/api/upload', (err, req, res, next) => {
  if (err instanceof multer.MulterError) {
    if (err.code === 'LIMIT_FILE_SIZE') {
      return res.json({ success: false, error: '文件大小不能超过 5MB' });
    }
    return res.json({ success: false, error: err.message });
  }
  if (err) {
    return res.json({ success: false, error: err.message });
  }
  next();
});

// ── Start Server ───────────────────────────────────────────────────────────

app.listen(PORT, () => {
  console.log(`Restaurant Menu server running at http://localhost:${PORT}`);
  console.log(`Menu page: http://localhost:${PORT}/menu.html`);
  console.log(`Admin panel: http://localhost:${PORT}/admin/login.html`);
});
